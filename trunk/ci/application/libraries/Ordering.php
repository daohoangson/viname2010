<?php

class Ordering {
	function sort(&$result) {
		$info = $this->extractUrl(current_url());
		if (!empty($info)) {
			if ($info['field'] == 'popularity') $info['field'] = 'count'; // rename manually, I know it's dirty
			usort($result,create_function('$a,$b',
				'$v1 = $a->' . $info['field'] . ';'
				. '$v2 = $b->' . $info['field'] . ';'
				. 'return ($v1 == $v2?0:($v1 ' . ($info['dir'] == 'desc'?'<':'>') . ' $v2?1:-1));'
			));
		}
	}
	
	function buildLink($field,$dir = null) {
		if ($dir === null) {
			$info = $this->extractUrl(current_url());
			if (!empty($info) AND $info['field'] == $field) {
				if ($info['dir'] == 'desc') {
					$dir = 'asc';
				} else {
					$dir = 'desc';
				}
			} else {
				$dir = 'desc';
			}
		}
		
		return $this->truncateUrl(current_url()) . '/orderby-' . $field . '/dir-' . $dir;
	}
	
	function truncateUrl($url) {
		return preg_replace('/\/(orderby|dir)\-[^\/]+/','',$url);
	}
	
	function extractUrl($url) {
		if (preg_match('/orderby-([^\/]+)(\/dir-([^\/]+))?/',$url,$matches)) {
			return array('field' => $matches[1], 'dir' => @$matches[3]);
		} else {
			return array();
		}
	}
}

?>