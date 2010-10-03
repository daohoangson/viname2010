<?php

class User extends Model {
	var $saltLength = 3;
	
	function get($conditions,$limit = null) {
		foreach ($conditions as $column => $value) {
			$this->db->where($column,$value);
		}
		if ($limit !== null) {
			$this->db->limit($limit);
		}
		$query = $this->db->get('users');
		return $query->result();
	}
	
	function only($conditions) {
		$result = $this->get($conditions,1);
		if (count($result) == 1) {
			return $result[0];
		} else {
			return false;
		}
	}
	
	function save($data) {
		return $this->db->insert('users',$data);
	}
	
	function loggedIn($user) {
		$data = array(
			'logged_in' => time(),
		);
		$this->db->where('user_id',$user->user_id);
		$this->db->update('users',$data);
	}
	
	function generateSalt() {
		$salt = '';
		for ($i = 0; $i < $this->saltLength; $i++) {
			$salt .= chr(rand(33,126));
		}
		return $salt;
	}
	
	function hashPassword($password,$salt) {
		return md5(md5($password).$salt);
	}
}

?>