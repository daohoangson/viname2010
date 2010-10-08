<?php

class Dbconfig extends Model {
	function get($key) {
		$arranged = array();
		if (!is_array($key)) $key = array($key); // arrayize the key
		foreach ($key as $singlekey) {
			$this->db->or_where('key',$singlekey);
			$arranged[$singlekey] = false;
		}
		$query = $this->db->get('dbconfig');
		
		$result = $query->result();
		for ($i=0; $i < count($result); $i++) { 
			if (!empty($result[$i]->serialized)) {
				$result[$i]->value = unserialize($result[$i]->value);
			}
			$arranged[$result[$i]->key] = $result[$i]->value;
		}
		unset($result);
		
		return $arranged;
	}
	
	function fetch($key,$default = false) {
		$result = $this->get($key);
		if ($result[$key] === false) $result[$key] = $default;
		return $result[$key];
	}
	
	function save($key,$value,$serialize = 0) {
		if ($serialize OR is_array($value)) {
			$value = serialize($value);
			$serialized = 1;
		} else {
			$serialized = 0;
		}
		$key = $this->db->escape($key);
		$value = $this->db->escape($value);
		$user_id = $this->authentication->getUser('user_id');
		$time = time();
		$this->db->query("
			REPLACE INTO `" . $this->db->dbprefix('dbconfig') . "`
			(`key`,`value`,`serialized`,`updator`,`updated`)
			VALUES
			($key,$value,$serialized,$user_id,$time)
		");
	}
}

?>