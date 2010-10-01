<?php

class Shared extends Controller {
	var $layout = 'default';
	var $js = array();
	var $css = array();
	
	function Shared() {
		parent::Controller();
		
		$this->_css('assets/css/global.css');
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
		self::_js_static('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js');
		self::_js_static('assets/js/jquery.rollup.js');
	}
	
	static function _js_static($js, $type = 'link') {
		get_instance()->_js($js,$type);
	}
	
	static function _css_static($css, $type = 'link') {
		get_instance()->_css($css,$type);
	}
}

?>