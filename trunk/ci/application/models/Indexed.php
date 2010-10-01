<?php

class Indexed extends Model {
	function utilEscape($text) {
		return $this->unicoder->asciiAccent($text);
	}
	
	function utilEscapeArray($arrayOfText) {
		return array_map(array($this,'utilEscape'),$arrayOfText);
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
	
	function full_names($limit = 50) {
		$query = $this->db->query("
			SELECT original_full_name AS full_name, `count`
			FROM `" . $this->db->dbprefix('index') . "`
			ORDER BY `count` DESC
			LIMIT $limit
		");
		
		return $query->result();
	}
	
	function family_names($limit = 50) {
		$this->db->order_by('count','desc');
		$this->db->limit($limit);
		$query = $this->db->get('index_family_names');
		return $query->result();
	}
	
	function names($limit = 50) {
		$this->db->order_by('count','desc');
		$this->db->limit($limit);
		$query = $this->db->get('index_names');
		return $query->result();
	}
	
	function middle_names($limit = 50) {
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
}

?>