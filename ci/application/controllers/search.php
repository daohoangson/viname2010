<?php

require_once(dirname(__FILE__) . '/shared.php');

class Search extends Shared {
	var $cacheResult = true;
	
	function route() {
		$args = func_get_args();
		if (count($args) == 0) {
			$this->index();
		} else if (method_exists($this,$args[0])) {
			call_user_func_array(array($this,$args[0]),array_slice($args,1));
		} else {
			call_user_func_array(array($this,'view'),$args);
		}
	}
	
	function index() {		
		$this->load->view('search/index');
	}
	
	function more($encoded) {
		$q = $this->unicoder->base64_decode($encoded);
		define('INTERNAL_QUERY',$q);
		$this->submit();
	}
	
	function submit() {
		$this->load->model('Result');
		
		if (defined('INTERNAL_QUERY')) {
			$q = INTERNAL_QUERY;
			$q = $this->input->xss_clean($q);
		} else {
			$q = $this->unicoder->ucwords(trim($this->input->post('q',true)));
		}
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
		
		$resultMax = 250;
		if (count($result) > $resultMax) {
			$result = array_slice($result,0,$resultMax);
		}
		
		$id = $this->Result->save($q,$result,$filters,$this->authentication->getUser('user_id'));
		$this->_gotoView($id,$q);
	}
	
	function _gotoView($id,$q = null) {
		if (!empty($q)) {
			$q_safe = preg_replace('/[^a-z]/i','_',$this->unicoder->removeAccent($q));
		} else {
			$q_safe = '';
		}
		$this->_redirect('/search/' . $id . '/' . $q_safe);
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
		$perpage_default = 25;
		
		if (!empty($data)) {
			$args = $this->_args(1);
			
			$filters = array('full_name' => array(),'family_name' => array(),'middle_name' => array(),'name' => array(),'gender' => array());
			$active_filters = array();
			$result =& $data->result;
			
			// get name filters from search result
			foreach ($data->filters as $filter => $count) {
				$tmp = $this->_readFilter($filter);
				$filters[$tmp['type']][$tmp['key']] = $tmp;
			}
			// process active filter (from url)
			if (!empty($args['filter'])) {
				if (!is_array($args['filter'])) {
					$args['filter'] = array($args['filter']);
				}
				foreach ($args['filter'] as $filter) {
					$tmp = $this->_readFilter($filter,true);
					$active_filters[$tmp['type']][$tmp['key']] = $tmp;
					$filters[$tmp['type']][$tmp['key']] = $tmp;
				}
				$result = array_values($result);
			}
			$family_names = array();
			$names = array();
			$gender0 = 0;
			$gender1 = 0;
			$gender0a = 0;
			$gender1a = 0;
			// $do_names_filters = (count($result) > $perpage_default); // only do names filters if there are more than 1 page left
			$do_names_filters = true;
			// add real time filters
			foreach (array_keys($result) as $recordkey) {
				$record =& $result[$recordkey];
				
				$words = explode(' ',$record->full_name);
				$family_name = (count($words) > 1)?($words[0]):('');
				$family_name_ascii = $this->unicoder->asciiAccent($family_name);
				$middle_name = (count($words) > 2)?($words[count($words) - 2]):('');
				$middle_name_ascii = $this->unicoder->asciiAccent($middle_name);
				$name = $words[count($words) - 1];
				$name_ascii = $this->unicoder->asciiAccent($name);
				
				$ignored = false;
				$counted = array();
				foreach ($filters as $filtertype => &$filters_of_type) {
					foreach ($filters_of_type as $filterkey => &$the_filter) {
						$is_activated = isset($active_filters[$filtertype][$filterkey]);
						switch ($filtertype) {
							case 'family_name':
							case 'middle_name':
							case 'name':
							case 'full_name':
								if ((empty($the_filter['family_name']) OR $family_name_ascii == $the_filter['family_name_ascii'])
									AND (empty($the_filter['middle_name']) OR $middle_name_ascii == $the_filter['middle_name_ascii'])
									AND (empty($the_filter['name']) OR $name_ascii == $the_filter['name_ascii'])) {
										$the_filter['count']++;
										$counted[$filterkey] =& $the_filter['count'];
									} else if ($is_activated) {
										$ignored = true;
									}
								break;
							case 'gender':
								if ($record->gender == -1 OR $record->gender == $the_filter['gender']) {
									$the_filter['count']++;
									$counted[$filterkey] =& $the_filter['count'];
								} else if ($is_activated) {
									$ignored = true;
								}
								break;
						}
					}
				}
				if ($ignored) {
					foreach ($counted as &$count_ref) {
						$count_ref--;
					}
					unset($result[$recordkey]);
					continue;
				}
				
				if ($do_names_filters) {
					if (!isset($names[$name_ascii])) {
						$names[$name_ascii] = array(
							'original' => $name,
							'count' => 1,
						);
					} else {
						$names[$name_ascii]['count']++;
					}
					
					if (!empty($family_name)) {
						
						if (!isset($family_names[$family_name_ascii])) {
							$family_names[$family_name_ascii] = array(
								'original' => $family_name,
								'count' => 1,
							);
						} else {
							$family_names[$family_name_ascii]['count']++;
						}
					}
				}
				if ($record->gender == -1) {
					$gender0++;
					$gender1++;
				} else if ($record->gender == 1) {
					$gender1++;
					$gender1a++;
				} else {
					$gender0++;
					$gender0a++;
				}
			}
			// name filters
			$countCompareFunction = create_function('$a,$b','return $a["count"] < $b["count"];');
			usort($names,$countCompareFunction);
			usort($family_names,$countCompareFunction);
			for ($i = 0; $i < min(count($names),10); $i++) {
				$filter = '... ... ' . $names[$i]['original'];
				$tmp = $this->_readFilter($filter);
				$tmp['count'] = $names[$i]['count'];
				$filters[$tmp['type']][$tmp['key']] = $tmp;
			}
			for ($i = 0; $i < min(count($family_names),10); $i++) {
				$filter = $family_names[$i]['original'] . ' ... ...';
				$tmp = $this->_readFilter($filter);
				$tmp['count'] = $family_names[$i]['count'];
				$filters[$tmp['type']][$tmp['key']] = $tmp;
			}
			// gender filters
			if ($gender0*$gender1 > 0 AND ($gender1a > 0 OR $gender0a > 0)) {
				// there are 2 genders
				// and must be something absoulte
				foreach (array(0,1) AS $gender) {
					$filter = "$gender";
					$tmp = $this->_readFilter($filter);
					$tmp['count'] = ${'gender' . $gender};
					$filters[$tmp['type']][$tmp['key']] = $tmp;
				}
			}

			$this->load->library('paginator');
			$this->load->library('ordering');
			$this->load->view('search/view',array(
				'id' => $data->id,
				'q' => $data->q,
				'result' => array_values($result),
				'perpage_default' => $perpage_default,
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
		$parts = explode(' ',$raw?$this->unicoder->base64_decode($filter):$filter);
		
		if (count($parts) == 3) {
			// name filter
			$words = $parts;
			$tmp['type'] = 'full_name';
			if ($words[0] != '...') $tmp['family_name'] = $words[0]; else $tmp['family_name'] = '';
			if ($words[1] != '...') $tmp['middle_name'] = $words[1]; else $tmp['middle_name'] = '';
			if ($words[2] != '...') $tmp['name'] = $words[2]; else $tmp['name'] = '';
			$missing = 0; $last = null;
			foreach (array_keys($tmp) as $key) {
				if (empty($tmp[$key])) $missing++; else $last = $key;
				$tmp["{$key}_ascii"] = $this->unicoder->asciiAccent($tmp[$key]);
			}
			if ($missing == 2) {
				// only 1 element present
				// update filter type
				$tmp['type'] = $last;
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
		$tmp['key'] = $raw?$filter:$this->unicoder->base64_encode($filter);
		$tmp['count'] = 0;
		
		return $tmp;
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