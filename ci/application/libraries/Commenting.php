<?php

class Commenting {
	function __construct() {
		$this->ci =& get_instance();
		$this->ci->load->database();
		$this->ci->load->model('comment/Comment');
		$this->ci->lang->load('comment');
	}
	
	function getComments($type,$id,$form = false) {
		$post_id = $this->generatePostid($type, $id);
		// pre-process comment
		if (!empty($form)) {
			$comment = $this->ci->input->post('comment',true);
			if (!empty($comment)) {
				if ($this->ci->authentication->isLoggedIn()) {
					$data = array(
						'post_id' => $post_id,
						'user_id' => $this->ci->authentication->getUser('user_id'),
						'username' => $this->ci->authentication->getUser('username'),
						'comment' => $comment,
						'submitted' => time(),
					);
					if (method_exists($this->ci,'_flash')) {
						$this->ci->_flash($this->ci->lang->line('comment_submitted'));
					}
					$this->ci->Comment->save($data);
					$comment = '';
				} else {
					if (method_exists($this->ci,'_flash')) {
						$this->ci->_flash($this->ci->lang->line('comment_must_login'));
					}
				}
			}
		}
		
		$commentRecords = $this->ci->Comment->get(array('post_id' => $post_id));
		$comments = '';
		foreach ($commentRecords as $commentRecord) {
			$comments .= $this->ci->load->view('comment/comment',array(
				'comment' => $commentRecord,
			),true);
		}
		if (empty($form)) return $comments;
		
		$commentForm = '';
		if ($this->ci->authentication->isLoggedIn()) {
			$commentForm = $this->ci->load->view('comment/commentForm',array(
				'post_id' => $post_id,
				'comment' => $comment,
			),true);
		}
		return array($comments,$commentForm);
	}
	
	function generatePostid($type,$id) {
		return "$type$id";
	}
}

?>