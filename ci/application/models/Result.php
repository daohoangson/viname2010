<?php

class Result extends Model {
	var $idDomain = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
	var $idLength = 6;
	var $idMax = 30;
	
	function save($q,$result,$filters,$user_id = null) {
		// find a unique id
		$id = $this->_generateId($q);
		
		$data = array(
			'id' => $id,
			'hash' => $this->_generateHash($q),
			'user_id' => intval($user_id),
			'q' => $q,
			'result' => serialize($result),
			'filters' => serialize($filters),
			'generated' => time()
		);
		$this->db->insert('results',$data);
		
		return $id;
	}
	
	function duplicate($id,$q,$user_id = null) {
		$this->db->where('id',$id);
		$this->db->where('user_id !=',intval($user_id));
		$query = $this->db->get('results');
		$result = $query->result();
		if (count($result) == 1) {
			// found something to duplicate
			$old = $result[0];
			$newid = $this->_generateId($q);
			$data = array(
				'id' => $newid,
				'hash' => $old->hash,
				'user_id' => intval($user_id),
				'q' => $q,
				'result' => $old->result,
				'filters' => $old->filters,
				'generated' => time(),
			);
			$this->db->insert('results',$data);
			
			return $newid;
		} else {
			return $id;
		}
	}
	
	function load($id) {
		$this->db->where('id',$id);
		$query = $this->db->get('results');
		$result = $query->result();
		if (count($result) == 1) {
			$result[0]->result = unserialize($result[0]->result);
			$result[0]->filters = unserialize($result[0]->filters);
			return $result[0];
		} else {
			return false;
		}
	}
	
	function hit($id) {
		$this->db->query("
			UPDATE `" . $this->db->dbprefix('results') . "`
			SET `hit` = `hit` + 1
			WHERE `id` = " . $this->db->escape($id) . "
		");
	}
	
	function existed($q) {
		$hash = $this->_generateHash($q);
		$this->db->where('hash',$hash);
		$this->db->select('id');
		$query = $this->db->get('results');
		$result = $query->result();
		if (count($result) > 0) {
			return $result[0]->id;
		} else {
			return false;
		}
	}
	
	function _verifyId($id) {
		$this->db->where('id',$id);
		return $this->db->count_all_results('results') > 0;
	}
	
	function _generateId($q) {
		do {
			$id = '';
			$idDomainLength = strlen($this->idDomain);
			for ($i=0; $i < $this->idLength; $i++) { 
				$id .= $this->idDomain[rand(0,$idDomainLength - 1)];
			}
		} while ($this->_verifyId($id) == true);
		return $id;
	}
	
	function _generateHash($q) {
		$q = trim(preg_replace('/\s+/',' ',$this->unicoder->asciiAccent($q)));
		return md5($q);
	}
}

?>