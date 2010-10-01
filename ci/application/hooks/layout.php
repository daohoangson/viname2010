<?php 

class Layout {
	function doLayout() {
		global $OUT;
		
		$CI =& get_instance();
		$output = $CI->output->get_output();

		if (isset($CI->layout)) {
			if (!preg_match('/(.+).php$/', $CI->layout)) {
				$CI->layout .= '.php';
			}
		
			$requested = BASEPATH . 'application/views/layouts/' . $CI->layout;
			$default = BASEPATH . 'application/views/layouts/default.php';
			
			if (file_exists($requested)) {
				$layout = $CI->load->file($requested, true);
			} else {
				$layout = $CI->load->file($default, true);
			}
			
			$view = str_replace("{content}", $output, $layout);
			
			// process javascript/css stuff
			$header = '';
			if (!empty($CI->js)) {
				foreach ($CI->js as $js) {
					if (is_array($js) AND isset($js['link'])) {
						$header .= "<script type=\"text/javascript\" src=\"$js[link]\"></script>\n";
					} else {
						$header .= "<script type=\"text/javascript\">$js</script>\n";
					}
				}
			}
			if (!empty($CI->css)) {
				foreach ($CI->css as $css) {
					if (is_array($css) AND isset($css['link'])) {
						$header .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css[link]\" />\n";
					} else {
						$header .= "<style>$css</style>\n";
					}
				}
			}
			$view = str_replace("{header}", $header, $view);
		} else {
			$view = $output;
		}
		
		$OUT->_display($view);
	}
}

?>