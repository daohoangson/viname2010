<?php

class Comment extends Model {
	function get($conditions,$limit = null) {
		foreach ($conditions as $column => $value) {
			$this->db->where($column,$value);
		}
		if ($limit !== null) {
			$this->db->limit($limit);
		}
		$query = $this->db->get('comments');
		return $query->result();
	}
	
	function save($data) {
		return $this->db->insert('comments',$data);
	}
}

?>