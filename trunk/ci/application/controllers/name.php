<?php

require(dirname(__FILE__) . '/shared.php');

class Name extends Shared {
	function view($hash) {
		$full_name = $this->Indexed->utilHash($hash,false);
		$indexed = $this->Indexed->get(array('full_name' => $full_name));
		if (count($indexed) == 0) {
			$this->load->view('common/error',array('message' => $this->lang->line('no_result_found')));
		} else {
			$this->load->library('commenting');
			list($comments,$commentForm) = $this->commenting->getComments('name',$full_name,true);
			$this->load->view('name/view',array(
				'indexed' => $indexed,
				'comments' => $comments,
				'commentForm' => $commentForm,
			));
		}
	}
	
	function family($hash) {
		$family_name = $this->Indexed->utilHash($hash,false);
		$indexed = $this->Indexed->generateList('same_family',array('family' => $family_name,'ascii' => true),11);
		if (count($indexed) == 0) {
			$this->load->view('common/error',array('message' => $this->lang->line('no_result_found')));
		} else {
			$this->load->view('name/family',array(
				'indexed' => $indexed,
			));
		}
	}
	
	function only($hash) {
		$name = $this->Indexed->utilHash($hash,false);
		$indexed = $this->Indexed->generateList('same_name',array('name' => $name,'ascii' => true),11);
		if (count($indexed) == 0) {
			$this->load->view('common/error',array('message' => $this->lang->line('no_result_found')));
		} else {
			$this->load->view('name/only',array(
				'indexed' => $indexed,
			));
		}
	}
}

?>