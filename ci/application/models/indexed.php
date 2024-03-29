<?php

class Indexed extends Model {
	var $utilHashCharacters	= "abcdefghijklmnopqrstuvwxyz '`?~.^(*-";
	var $utilHashSymbols	= "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	
	function get($conditions,$limit = null) {
		foreach ($conditions as $column => $value) {
			$this->db->where($column,$value);
		}
		if ($limit !== null) {
			$this->db->limit($limit);
		}
		$query = $this->db->get('index');
		return $query->result();
	}
	
	function getStats() {
		$stats = array();
		
		$stats['names_count'] = $this->db->count_all('names');
		$this->db->where('gender',0);
		$stats['names_male_count'] = $this->db->count_all_results('names');
		$this->db->where('gender',1);
		$stats['names_female_count'] = $this->db->count_all_results('names');
		$this->db->where('gender',-1);
		$stats['names_unknown_gender_count'] = $this->db->count_all_results('names');
		$stats['index_count'] = $this->db->count_all('index');
		$stats['index_family_names_count'] = $this->db->count_all('index_family_names');
		$stats['index_names_count'] = $this->db->count_all('index_names');
		
		return $stats;
	}
	
	function generateList($type,$data = array(),$limit = 50) {
		if (!in_array($type,array('famous','same_family','same_name'))) {
			return array();
		}
		
		$result = call_user_func(array($this,$type),$data,$limit);
		return $result;
	}
	
	function famous($data,$limit) {
		if (empty($data['full_names'])) return array();
		
		$query = $this->db->query("
			SELECT *
			FROM " . $this->db->dbprefix('names') . "
			WHERE full_name IN (" . implode(',',array_map(array($this->db,'escape'),$data['full_names'])) . ")
				AND score > 500
			ORDER BY score DESC
			LIMIT $limit
		");
		
		return $query->result();
	}
	
	function same_family($data,$limit) {
		if (empty($data['family'])) return array();
		$family = $data['family'];
		if (empty($data['ascii'])) $family = $this->unicoder->asciiAccent($family);
		$query = $this->db->query("
			SELECT *
			FROM `" . $this->db->dbprefix('index') . "`
			WHERE family_name = ?
			ORDER BY average_score DESC
			LIMIT $limit
		",array($family));
		
		return $query->result();
	}
	
	function same_name($data,$limit) {
		if (empty($data['name'])) return array();
		$name = $data['name'];
		if (empty($data['ascii'])) $name = $this->unicoder->asciiAccent($name);
		$query = $this->db->query("
			SELECT *
			FROM `" . $this->db->dbprefix('index') . "`
			WHERE name = ?
			ORDER BY average_score DESC
			LIMIT $limit
		",array($name));
		
		return $query->result();
	}
	
	function getList($type,$limit = 50,$getValue = false) {
		if (!in_array($type,array('full_names','family_names','names','middle_names'))) {
			return array();
		}
		
		$result = call_user_func(array($this,$type),$limit);
		$list = array();
		$columnname = substr($type,0,-1);
		foreach ($result as $key => $value) {
			if (!$getValue) {
				$list["{$value->$columnname}"] = $value->count;
			} else {
				$list[] = $value->$columnname;
			}
		}
		return $list;
	}
	
	function full_names($limit) {
		$query = $this->db->query("
			SELECT original_full_name AS full_name, `count`
			FROM `" . $this->db->dbprefix('index') . "`
			ORDER BY `count` DESC
			LIMIT $limit
		");
		
		return $query->result();
	}
	
	function family_names($limit) {
		$this->db->order_by('count','desc');
		$this->db->limit($limit);
		$query = $this->db->get('index_family_names');
		return $query->result();
	}
	
	function names($limit) {
		$this->db->order_by('count','desc');
		$this->db->limit($limit);
		$query = $this->db->get('index_names');
		return $query->result();
	}
	
	function middle_names($limit) {
		// this method is server intensive!
		// shouldn't be used
		$query = $this->db->query("
			SELECT original_middle_name AS middle_name, SUM(`count`) AS `count`
			FROM `" . $this->db->dbprefix('index') . "`
			WHERE middle_name IS NOT NULL
			GROUP BY middle_name
			ORDER BY `count` DESC
		");
		$resultAll = $query->result();
		// the MySQL query runs much slower with LIMIT statement. I have no clue :'(
		$result = array();
		foreach ($resultAll as $key => $record) {
			$result[$key] = $record;
			if (count($result) > $limit) break;
		}
		return $result;
	}
	
	function utilEscape($text) {
		return $this->unicoder->asciiAccent($text);
	}
	
	function utilEscapeArray($arrayOfText) {
		return array_map(array($this,'utilEscape'),$arrayOfText);
	}
	
	function utilHash($text, $encoding = true) {
		$hash = '';
		
		// prepare
		if (empty($this->utilHashCharactersArray)) {
			$this->utilHashCharactersArray = preg_split('//',$this->utilHashCharacters);
			$this->utilHashSymbolsArray = preg_split('//',$this->utilHashSymbols);
		}
		// prepare part 2 (encoding or decoding?)
		if ($encoding) {
			$text = $this->utilEscape($text);
			$map = array_combine($this->utilHashCharactersArray,$this->utilHashSymbolsArray);
		} else {
			$map = array_combine($this->utilHashSymbolsArray,$this->utilHashCharactersArray);
		}
		// work
		$len = strlen($text);
		for ($i = 0; $i < $len; $i++) {
			$hash .= isset($map[$text[$i]])?$map[$text[$i]]:'';
		}
		return $hash;
	}
}

?>