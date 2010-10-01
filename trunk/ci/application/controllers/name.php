<?php

require(dirname(__FILE__) . '/shared.php');

class Name extends Shared {
	function view($name) {
		$this->load->model('Namemodel');
		var_dump($this->Namemodel->get($name));
	}
}

?>