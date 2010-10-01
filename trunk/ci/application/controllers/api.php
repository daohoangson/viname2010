<?php

class Api extends Controller {
	private $outputFormat = 'json';
	private $limitMax = 5000;
	
	function getList($type,$limit = 50) {
		$limit = min($this->limitMax,intval($limit));
		$this->_output($this->Indexed->getList($type,$limit,true));
	}
	
	/**
	 * Outputs data in the specified output format.
	 */
	function _output($data) {
		$output = array(
			'response' => $data,
			'requested' => uri_string(),
		);
		switch ($this->outputFormat) {
			case 'json':
				echo json_encode($output);
				break;
			case 'xml':
				echo $this->load->view('layouts/xml',array('data' => $output),true);
				break;
		}
		exit; // stop executing and flush output
	}
	
	/**
	 * Generates generic error messages.
	 */
	function _error($message = null) {
		if ($message === null) $message = $this->lang->line('Invalid API request');
		$this->_output(array('error' => $message));
	}
	
	/**
	 * Router for JSON requests
	 */
	function json() {
		$args = func_get_args();
		if (count($args) > 0) {
			$this->outputFormat = 'json';
			call_user_func_array(array($this,$args[0]),array_slice($args,1));
		}
		return; // make sure it stops
	}
	
	/**
	 * Router for XML requests
	 */
	function xml() {
		$args = func_get_args();
		if (count($args) > 0) {
			$this->outputFormat = 'xml';
			call_user_func_array(array($this,$args[0]),array_slice($args,1));
		}
		return; //make sure it stops
	}
}

?>