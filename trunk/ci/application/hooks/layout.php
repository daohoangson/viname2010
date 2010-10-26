<?php 

class Layout {
	function doLayout() {
		global $OUT;
		
		$CI =& get_instance();
		$output = $CI->output->get_output();

		if (isset($CI->layout) AND !$CI->_isAjax()) {
			// process javascript/css stuff
			$head = '';
			if (!empty($CI->js)) {
				foreach ($CI->js as $js) {
					if (is_array($js) AND isset($js['link'])) {
						$head .= "<script type=\"text/javascript\" src=\"$js[link]\"></script>\n";
					} else {
						$head .= "<script type=\"text/javascript\">$js</script>\n";
					}
				}
			}
			if (!empty($CI->css)) {
				foreach ($CI->css as $css) {
					if (is_array($css) AND isset($css['link'])) {
						$head .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css[link]\" />\n";
					} else {
						$head .= "<style>$css</style>\n";
					}
				}
			}

			$view = $CI->load->view('layouts/' . $CI->layout,array(
				'output' => $output,
				'head' => $head,
				'message' => $CI->_flash(),
			),true);
		} else {
			$view = json_encode(array(
				'url' => current_url(),
				'message' => $CI->_flash(),
				'html' => $output,
			));
		}
		
		$OUT->_display($view);
	}
}

?>