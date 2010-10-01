<?php

require_once(dirname(__FILE__) . '/shared.php');

class Search extends Shared {
	function index() {		
		$this->load->view('search/index');
	}
	
	function submit() {
		$this->load->model('Result');
		
		$q = trim($this->input->post('q',true));
		if (empty($q)) return false;
		$existedId = $this->Result->existed($q);
		if ($existedId !== false) redirect('/search/view/' . $existedId);
		
		// prepare parsing names
		$family_names = $this->Indexed->getList('family_names',50,true);
		$family_names_ascii = $this->Indexed->utilEscapeArray($family_names);
		$names = $this->Indexed->getList('names',50,true);
		$names_ascii = $this->Indexed->utilEscapeArray($names);
		$words = explode(' ',$q);
		$words_ascii = $this->Indexed->utilEscapeArray($words);
		// query stuff
		$query_family_name = array();
		$query_middle_name = array();
		$query_name = array();
		// look for a popular family name
		$family_names_found = array();
		foreach ($words_ascii as $key => $ascii) {
			if (in_array($ascii,$family_names_ascii)) {
				// this is a family name
				$family_names_found[] = $ascii;
			}
		}
		// look for a popular name
		$names_found = array();
		foreach ($words_ascii as $key => $ascii) {
			if (in_array($ascii,$names_ascii)) {
				// this is a name
				$names_found[] = $ascii;
			}
		}
		// build query (family name)
		if (count($family_names_found)) {
			// we found something, lucky
			$query_family_name[] = $family_names_found[0];
		}
		// build query (name)
		if (count($names_found)) {
			// we found a name, at least
			$query_name[] = $names_found[count($names_found) - 1];
			// take the right before word as middle name
			for ($i = 0; $i < count($words_ascii) - 1; $i++) {
				if ($words_ascii[$i + 1] == $query_name[count($query_name) - 1]) {
					$query_middle_name[] = $words_ascii[$i];
					break;
				}
			}
		} else {
			// use the last word (assume it's name)
			$query_name_added = false;
			for ($i = count($words_ascii) - 1; $i >= 0; $i--) {
				if (!in_array($words_ascii[$i],$query_family_name)) {
					if (!$query_name_added) {
						$query_name[] = $words_ascii[$i];
						$query_name_added = true;
					} else {
						// the the right before word as middle name
						$query_middle_name[] = $words_ascii[$i];
						break;
					}
				}
			}
		}
		// build query (middle name)
		// put the others into middle name search
		$query_name_and_family_name = array_merge($query_name,$query_family_name);
		foreach ($words_ascii as $ascii) {
			if (!in_array($ascii,$query_name_and_family_name)) {
				$query_middle_name[] = $ascii;
			}
		}
		$query_family_name = array_unique($query_family_name);
		$query_middle_name = array_unique($query_middle_name);
		$query_name = array_unique($query_name);
		
		// QUERY THE DATABASE
		$resultAll = array();
		if (!empty($query_family_name) OR !empty($query_middle_name) OR !empty($query_name)) {
			foreach ($query_middle_name as $mn) {
				if (!empty($query_family_name)) {
					// keep at least 2 matched elements
					$resultAll["xyz $mn ..."] = $this->_queryIndex($query_family_name,$mn,null);
				}
				foreach ($query_name as $n) {
					$resultAll["xyz $mn $n"] = $this->_queryIndex($query_family_name,$mn,$n);
					$resultAll["... $mn $n"] = $this->_queryIndex(null,$mn,$n);
				}
			}
			foreach ($query_name as $n) {
				$resultAll["xyz ... $n"] = $this->_queryIndex($query_family_name,null,$n);
			}
		}
		
		// build result
		$result = array();
		foreach ($resultAll as $resultset => $resultAlmost) {
			foreach ($resultAlmost as $record) {
				if (isset($result[$record->full_name])) {
					$theoldone =& $result[$record->full_name];
					// detect if this is the same record
					if ($theoldone->gender == $record->gender
						AND $this->unicoder->removeAccent($theoldone->full_name) == 
							$this->unicoder->removeAccent($record->original_full_name)) {
						// same
						continue;
					} else {
						// different
						$theoldone->average_score = ($theoldone->average_score*$theoldone->count + $record->average_score*$record->count)
														/ ($theoldone->count + $record->count);
						$theoldone->count += $record->count;
					}
				} else {
					// new record
					$new = new StdClass();
					$new->full_name = $record->original_full_name;
					$new->gender = $record->gender;
					$new->count = $record->count;
					$new->average_score = $record->average_score;
					$new->relevant = $this->_calculateRelevant($words_ascii,explode(' ',$record->full_name));
					$result[$record->full_name] = $new;
				}
			}
		}
		usort($result,array($this,'_resultSort'));
		
		$id = $this->Result->save($q,$result);
		redirect('/search/view/' . $id);
	}
	
	function view($id) {
		$this->load->model('Result');
		$this->load->view('search/view',array('data' => $this->Result->load($id)));
	}
	
	function _resultSort($a,$b) {
		if ($a->relevant == $b->relevant) {
			if ($a->count == $b->count) {
				if ($a->average_score == $b->average_score) {
					// shit!
					return 0;
				} else {
					return $a->average_score < $b->average_score?1:-1;
				}
			} else {
				return $a->count < $b->count?1:-1;
			}
		} else {
			return $a->relevant < $b->relevant?1:-1;
		}
	}
	
	function _calculateRelevant($source,$suspect) {
		$score = 0;
		$map = array();
		for ($i=0; $i < count($source); $i++) { 
			for ($j=0; $j < count($suspect); $j++) { 
				if ($suspect[$j] == $source[$i]) {
					$map[$i] = $j;
					break;
				}
			}
		}
		
		$score += 25 * (1 - abs(count($map)-count($source))/max(count($map),count($source)));
		$score += 25 * (1 - abs(count($map)-count($suspect))/max(count($map),count($suspect)));

		$ds = 50/pow(count($source),2)/2;
		foreach ($map as $i1 => $j1) {
			foreach ($map as $i2 => $j2) {
				if ($j2 - $j1 == $i2 - $i1) {
					$score += $ds*2;
				} else if (($j2 - $j1)*($i2-$i1) > 0) {
					$score += $ds;
				}
			}
		}
		
		return min(100,ceil($score));
	}
	
	function _queryIndex($family_name,$middle_name,$name) {
		if (is_array($family_name)) {
			foreach ($family_name as $fn) {
				$this->db->or_where('family_name',$fn);
			}
		}
		if ($middle_name != null) {
			$this->db->where('middle_name',$middle_name);
		}
		if ($name != null) {
			$this->db->where('name',$name);
		}
		$query = $this->db->get('index');
		return $query->result();
	}
}

?>