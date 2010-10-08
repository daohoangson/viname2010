<?php

class Shared extends Controller {
	var $layout = 'default';
	var $js = array();
	var $css = array();
	
	function __construct() {
		parent::__construct();
		
		$this->_jQuery();
		$this->_css('assets/css/global.css');
		
		$this->load->library('authentication');
	}
	
	function _args($ignore = 0) {
		$s = array_slice($this->uri->segment_array(),2 + $ignore); // skip controller, method
		$result = array();
		
		foreach ($s as $key => $value) {
			$parts = explode('-',$value);
			if (count($parts) == 2) {
				$key = $parts[0];
				$value = $parts[1];
			}
			if (!isset($result[$key])) {
				// nothing yet, add it directly
				$result[$key] = $value;
			} else {
				if (is_array($result[$key])) {
					// already an array
					// add this one too
					$result[$key][] = $value;
				} else {
					// still some value
					// transform to an array
					$result[$key] = array($result[$key],$value);
				}
			}
		}
		
		return $result;
	}
	
	function _redirect($target, $force = false) {
		if ($this->_isAjax()) {
			if ($force) {
				// use this to force reload the page instead of redirecting the ajax request
				echo json_encode(array('redirect' => $this->_site_url($target)));
				exit;
			}
			$this->session->set_flashdata('ajax','yes');
		}
		$referrer = $this->_referrer();
		if (!empty($referrer)) $this->_referrer($referrer);
		$message = $this->_flash();
		if (!empty($message)) $this->_flash($message);
		$url = $this->_site_url($target);
		redirect($url);
	}
	
	function _site_url($target,$prevent_loop = true) {
		if ($target == 'referrer') {
			$referrer = $this->_referrer();
			if (empty($referrer)) $referrer = $this->input->post('login_form_referrer');
			if (empty($referrer)) $referrer = $this->input->server('HTTP_REFERER');
			if ($prevent_loop AND $referrer == current_url()) {
				$referrer = site_url('');
			}
			return $referrer;
		} else {
			return site_url($target);
		}
	}
	
	function _referrer($target = null) {
		if ($target !== null) {
			// setting
			if ($target === 'current') {
				// use the current server referrer
				$this->session->set_flashdata('referrer',$this->input->server('HTTP_REFERER'));
			} else {
				// use the specified referrer
				$this->session->set_flashdata('referrer',$target);
			}
		} else {
			// getting
			return $this->session->flashdata('referrer');
		}
	}
	
	function _isAjax() {
		if ($this->input->get_post('ajax') OR $this->session->flashdata('ajax') == 'yes') {
			return true;
		} else {
			return false;
		}
	}
	
	function _flash($message = null) {
		if ($message != null) {
			// setting
			$this->session->set_flashdata('system_message',$message);
		} else {
			// getting
			$this->session->flashdata('system_message');
		}
	}
	
	function _js($js,$type = 'link') {
		if ($type == 'link') {
			$this->js[md5($js)] = array('link' => $js);
		} else {
			$this->js[] = $js;
		}
	}
	
	function _css($css,$type = 'link') {
		if ($type == 'link') {
			$this->css[md5($css)] = array('link' => $css);
		} else {
			$this->css[] = $css;
		}
	}
	
	static function _jQuery() {
		//self::_js_static('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
		self::_js_static('assets/js/jquery.js');
		self::_js_static('assets/js/jquery.rollup.js');
	}
	
	static function _js_static($js, $type = 'link') {
		get_instance()->_js($js,$type);
	}
	
	static function _css_static($css, $type = 'link') {
		get_instance()->_css($css,$type);
	}
}

class Admincp extends Shared {
	var $layout = 'admincp';
	
	function __construct() {
		parent::__construct();
		
		$segments = $this->uri->segment_array();
		if ($segments[1] != 'admin') die('Nothing here. Move on.');
		
		if (!$this->authentication->isLoggedIn()) {
			// request authentication
			$this->_referrer(current_url());
			$this->_redirect('/login');
		}
	}
}

?>