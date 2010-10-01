<?php

class Result extends Model {
	var $idDomain = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
	var $idLength = 6;
	
	function save($q,$result) {
		// find a unique id
		do {
			$id = '';
			$idDomainLength = strlen($this->idDomain);
			for ($i=0; $i < $this->idLength; $i++) { 
				$id .= $this->idDomain[rand(0,$idDomainLength - 1)];
			}
		} while ($this->_verifyId($id) == true);
		
		$data = array(
			'id' => $id,
			'hash' => $this->_generateHash($q),
			'q' => $q,
			'result' => serialize($result),
			'generated' => time()
		);
		$this->db->insert('results',$data);
		
		return $id;
	}
	
	function load($id) {
		$this->db->where('id',$id);
		$query = $this->db->get('results');
		$result = $query->result();
		if (count($result) == 1) {
			$result[0]->result = unserialize($result[0]->result);
			return $result[0];
		} else {
			return false;
		}
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
	
	function _generateHash($q) {
		$q = $this->unicoder->asciiAccent(trim(preg_replace('/\s+/',' ',$q)));
		return md5($q);
	}
}

?>