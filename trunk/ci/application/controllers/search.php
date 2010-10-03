<?php

require_once(dirname(__FILE__) . '/shared.php');

class Search extends Shared {
	var $cacheResult = true;
	
	function index() {		
		$this->load->view('search/index');
		
		
		$s1 = 'd-ao` hoang` so*n';
		$s2 = $s1;
		//var_dump(explode(' ',$s1),explode(' ',$s2),$this->_calculateRelevant($s1,$s2));exit;
	}
	
	function submit() {
		$this->load->model('Result');
		
		$q = $this->unicoder->ucwords(trim($this->input->post('q',true)));
		if (empty($q)) return false;
		if ($this->cacheResult) {
			$existedId = $this->Result->existed($q);
			if ($existedId !== false) {
				$id = $this->Result->duplicate($existedId,$q,$this->authentication->getUser('user_id'));
				$this->_gotoView($id,$q);
			}
		}
		$words = explode(' ',$q);
		$words_ascii = $this->Indexed->utilEscapeArray($words);
		$family_names = $this->Indexed->getList('family_names',100,true);
		$family_names_ascii = $this->Indexed->utilEscapeArray($family_names);
		
		//$resultAll = $this->_search1($q);
		$resultAll = $this->_search2($words,$words_ascii,$family_names_ascii);

		// build result
		$result = array();
		$filters = array();
		$genders = array();
		foreach ($resultAll as $resultset => $resultAlmost) {
			if (count($resultAlmost) > 0) {
				$filters[$resultset] = count($resultAlmost);
				foreach ($resultAlmost as $record) {
					if (isset($result[$record->full_name])) {
						$theoldone =& $result[$record->full_name];
						// detect if this is the same record
						if (in_array($record->gender,$genders[$record->full_name])
							AND $this->unicoder->removeAccent($theoldone->full_name) == 
								$this->unicoder->removeAccent($record->original_full_name)) {
							// same
							continue;
						} else {
							// different
							$theoldone->average_score = ($theoldone->average_score*$theoldone->count + $record->average_score*$record->count)
															/ ($theoldone->count + $record->count);
							$theoldone->count += $record->count;
							$theoldone->gender = -1;
							$genders[$record->full_name][] = $record->gender;
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
						$genders[$record->full_name] = array($record->gender);
					}
				}
			}
		}
		usort($result,array($this,'_resultSort'));
		
		$id = $this->Result->save($q,$result,$filters,$this->authentication->getUser('user_id'));
		$this->_gotoView($id,$q);
	}
	
	function _gotoView($id,$q = null) {
		if (!empty($q)) {
			$q_safe = preg_replace('/[^a-z]/i','_',$this->unicoder->removeAccent($q));
		} else {
			$q_safe = '';
		}
		$this->_redirect('/search/view/' . $id . '/' . $q_safe);
	}
	
	function _search2($words,$words_ascii,$family_names_ascii = array()) {
		$resultAll = array();
		
		switch (count($words_ascii)) {
			case 1:
				$resultAll["... ... $words[0]"] = $this->_queryIndex(null,null,$words_ascii[0]);
				if (in_array($words_ascii[0],$family_names_ascii)) {
					$resultAll["$words[0] ... ..."] = $this->_queryIndex($words_ascii[0],null,null);
				}
				$resultAll["... $words[0] ..."] = $this->_queryIndex(null,$words_ascii[0],null);
				break;
			case 2:
				$resultAll["$words[0] ... $words[1]"] = $this->_queryIndex($words_ascii[0],null,$words_ascii[1]);
				if (in_array($words_ascii[0],$family_names_ascii)) {	
					$resultAll["$words[0] $words[1] ..."] = $this->_queryIndex($words_ascii[0],$words_ascii[1],null);
				}
				$resultAll["... $words[0] $words[1]"] = $this->_queryIndex(null,$words_ascii[0],$words_ascii[1]);
				break;
			case 3:
				$resultAll["$words[0] $words[1] $words[2]"] = $this->_queryIndex($words_ascii[0],$words_ascii[1],$words_ascii[2]);
				if (in_array($words_ascii[1],$family_names_ascii)) {
					$resultAll["$words[1] ... $words[2]"] = $this->_queryIndex($words_ascii[1],null,$words_ascii[2]);
				}
				break;
			default:
				$name = $words[count($words) - 1];
				$middle = $words[count($words) - 2];
				$name_ascii = $words_ascii[count($words) - 1];
				$middle_ascii = $words_ascii[count($words) - 2];
				for ($i=0; $i < count($words) - 2; $i++) { 
					if ($i == 0 OR in_array($words_ascii[$i],$family_names_ascii)) {
						$resultAll["$words[0] $middle $name"] = $this->_queryIndex($words_ascii[0],$middle_ascii,$name_ascii);
					}
				}
		}
		
		return $resultAll;
	}
	
	function &_search1($q) {
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
				$resultAll["xyz $n ..."] = $this->_queryIndex($query_family_name,$n,null);
				$resultAll["xyz ... $n"] = $this->_queryIndex($query_family_name,null,$n);
			}
		}
		
		return $resultAll;
	}
	
	function view($id) {
		$this->load->model('Result');
		$data = $this->Result->load($id);
		
		if (!empty($data)) {
			$args = $this->_args(1);
			
			$filters = array();
			$active_filters = array();
			$result =& $data->result;
			
			// get name filters from search result
			foreach ($data->filters as $filter => $count) {
				$tmp = $this->_readFilter($filter);
				$tmp['count'] = $count;
				$filters['name'][$tmp['key']] = $tmp;
			}
			// process active filter (from url)
			if (!empty($args['filter'])) {
				if (!is_array($args['filter'])) {
					$args['filter'] = array($args['filter']);
				}
				foreach ($args['filter'] as $filter) {
					$tmp = $this->_readFilter($filter,true);

					foreach (array_keys($result) as $resultkey) {
						if ($tmp['type'] == 'name') {
							$words = explode(' ',$result[$resultkey]->full_name);
							$wordscount = count($words);
							if ((!empty($tmp['family_name']) AND $this->unicoder->asciiAccent($words[0]) != $tmp['family_name_ascii'])
								OR (!empty($tmp['middle_name']) AND $wordscount > 1 
									AND $this->unicoder->asciiAccent($words[$wordscount - 2]) != $tmp['middle_name_ascii'])
								OR (!empty($tmp['name']) AND $this->unicoder->asciiAccent($words[$wordscount - 1]) != $tmp['name_ascii'])
							) {
								unset($result[$resultkey]);
							}
						} else if ($tmp['type'] == 'gender') {
							if ($result[$resultkey]->gender != -1 AND $result[$resultkey]->gender != $tmp['gender']) {
								unset($result[$resultkey]);
							}
						}
					}
					$active_filters[$tmp['type']][$tmp['key']] = $tmp;
					if (!empty($filters[$tmp['type']])) {
						foreach (array_keys($filters[$tmp['type']]) as $filterkey) {
							if ($filterkey != $tmp['key']) {
								unset($filters[$tmp['type']][$filterkey]); // remove other filters in the same type
							}
						}
					}
				}
				$result = array_values($result);
			}
			// add real time filters
			if (!isset($active_filters['gender'])) {
				$resultcount = count($result);
				$genders = array();
				for ($i=0; $i < $resultcount; $i++) { 
					if (empty($genders[$result[$i]->gender])) {
						$genders[$result[$i]->gender] = 1;
					} else {
						$genders[$result[$i]->gender]++;
					}
				}
				if (count($genders) > 1) {
					// find more than 1 gender
					for ($j=0; $j < 2; $j++) { 
						if (!empty($genders[$j])) {
							$filter = "$j";
							$tmp = $this->_readFilter($filter);
							$tmp['count'] = $genders[$j] + @$genders[-1];
							$filters['gender'][$tmp['key']] = $tmp;
						}
					}
				}
			} else {
				$filters['gender'] = $active_filters['gender']; // for displaying
			}
			
			$this->load->library('paginator');
			$this->load->view('search/view',array(
				'id' => $data->id,
				'q' => $data->q,
				'result' => $result,
				'filters' => $filters,
				'active_filters' => $active_filters,
				'args' => $args,
			));
			$this->Result->hit($id);
		} else {
			$this->load->view('common/error',array('message' => $this->lang->line('search_too_old')));
		}
	}
	
	function _readFilter($filter,$raw = false) {
		$tmp = array();
		$parts = explode(' ',$raw?$this->_base64_decode($filter):$filter);
		
		if (count($parts) == 3) {
			// name filter
			$words = $parts;
			$tmp['type'] = 'name';
			if ($words[0] != '...') $tmp['family_name'] = $words[0];
			if ($words[1] != '...') $tmp['middle_name'] = $words[1];
			if ($words[2] != '...') $tmp['name'] = $words[2];
			foreach (array_keys($tmp) as $key) {
				$tmp["{$key}_ascii"] = $this->unicoder->asciiAccent($tmp[$key]);
			}
		} else if (count($parts) == 1 AND is_numeric($parts[0])) {
			// gender filter
			$tmp['type'] = 'gender';
			$tmp['gender'] = intval($parts[0]);
		} else if (count($parts) == 1) {
			// popularity filter
			$tmp['type'] = 'popularity';
			$tmp['popularity'] = $parts[0];
		}
		$tmp['key'] = $raw?$filter:$this->_base64_encode($filter);
		
		return $tmp;
	}
	
	function _base64_encode($text,$encoding = true) {
		$search = array('+','/','='); // unsafe characters
		$replace = array('.',':','_'); // somewhat safer ones
		if ($encoding) {
			return str_replace($search,$replace,base64_encode($text));
		} else {
			return base64_decode(str_replace($replace,$search,$text));
		}
	}
	
	function _base64_decode($text) {
		return $this->_base64_encode($text,false);
	}
	
	function _resultSort($a,$b) {
		if ($a->count < 50 AND $a->count < $b->count AND $a->relevant < 100) return 1;
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
		
		$score += 25 * (1 - (max(0,count($source) - count($map))/count($source)));
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
		} else if ($family_name !== null) {
			$this->db->where('family_name',$family_name);
		}
		if ($middle_name !== null) {
			$this->db->where('middle_name',$middle_name);
		}
		if ($name !== null) {
			$this->db->where('name',$name);
		}
		
		$null = 0;
		if ($family_name === null) $null++;
		if ($middle_name === null) $null++;
		if ($name === null) $null++;
		if ($null > 1) {
			$this->db->limit(1000);
			$this->db->order_by('count','desc');
		}
		
		
		$query = $this->db->get('index');
		return $query->result();
	}
}

?>