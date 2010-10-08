<?php

class Paginator {
	function build($items,$perpage_default = 50,$args = array()) {
		if (in_array('all',$args)) {
			$start = 0;
			$end = $items - 1;
			$paginator = '';
		} else {
			if (isset($args['perpage'])) $perpage = intval($args['perpage']);
			if (empty($perpage)) $perpage = $perpage_default;
			$pages = ceil($items/$perpage);
			if (isset($args['page'])) $page = intval($args['page']); else $page = 1;
			$page = max(1,min($pages,$page));
			$start = $perpage*($page - 1);
			$end = min($items - 1,$start + $perpage);
			if ($pages > 1) {
				$paginator = get_instance()->load->view('common/paginator',array(
					'page' => $page,
					'pages' => $pages,
					'perpage' => $perpage,
					'originalLink' => $this->truncateUrl(current_url()),
					'link' => $this->truncateUrl(current_url()) . '/page-%1$d' . ($perpage != $perpage_default?'perpage-%2$d':''),
				),true);
			} else {
				$paginator = '';
			}
		}
		return array($start,$end,$paginator);
	}
	
	function truncateUrl($url) {
		return preg_replace('/\/(page|perpage)\-\d+/','',$url);
	}
}

?>