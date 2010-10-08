<?php

require(dirname(__FILE__) . '/shared.php');

class News extends Admincp {
	function __construct() {
		parent::__construct();
		
		$this->load->model('Dbconfig');
		$this->lang->load('news');
	}
	
	function admin_fetch() {
		$url = $this->input->post('url');
		$isFeed = $this->input->post('isFeed');
		if (!empty($url)) {
			$config = $this->Dbconfig->get(array('news_traineds','news_clean','news_ignores_sub','news_ignores','news_pronouns'));
			$this->load->library('Reader');
			$this->reader->init($config);
			if ($isFeed) {
				$results = $this->reader->doFeed($url);
			} else {
				$results = array($this->reader->read($url));
			}
		}
		$this->load->view('news/admin_fetch',array(
			'url' => $url,
			'isFeed' => $isFeed,
			'results' => @$results,
		));
	}
	
	function admin_manage() {
		$words = $this->input->post('words');
		$delete = $this->input->post('delete');
		$changeds = array();
		if (!empty($words) OR !empty($delete)) {
			foreach (array('clean','ignores_sub','ignores','pronouns') as $key) {
				$tmp = $this->Dbconfig->fetch('news_' . $key,array());
				$changed = false;
				if (!empty($words[$key])) {
					foreach ($words[$key] as $words_of_key) {
						$words_of_key = explode("\n",$words_of_key);
						foreach ($words_of_key as $word) {
							$word = trim($word);
							if (!empty($word)) {
								$word = $this->unicoder->ucwords($word);
								$ascii = $this->unicoder->asciiAccent($word);
								if (!isset($tmp[$ascii])) {
									$tmp[$ascii] = $word;
									$changed = true;
								}
							}
						}
					}
				}
				if (!empty($delete[$key])) {
					foreach ($delete[$key] as $ascii64 => $confirm) {
						if ($confirm) {
							$ascii = $this->unicoder->base64_decode($ascii64);
							if (isset($tmp[$ascii])) {
								unset($tmp[$ascii]);
								$changed = true;
							}
						}
					}
				}
				if ($changed) {
					$changeds[] = $key;
					$this->Dbconfig->save('news_' . $key,$tmp);
				}
			}
			
			if (!$this->_isAjax() AND $this->_site_url('referrer',false) != current_url()) {
				$this->_redirect('referrer');
			}
		}
		
		if ($this->_isAjax()) {
			$this->load->view('news/admin_manage_changed',array('changeds' => $changeds));
		} else {
			$config = $this->Dbconfig->get(array('news_clean','news_ignores','news_ignores_sub','news_traineds','news_pronouns'));
			$this->load->view('news/admin_manage',array(
				'changeds' => $changeds,
				'ignores' => $config['news_ignores'],
				'ignores_sub' => $config['news_ignores_sub'],
				'clean' => $config['news_clean'],
				'pronouns' => $config['news_pronouns'],
				'traineds' => $config['news_traineds'],
			));
		}
	}
	
	function admin_train($host = false) {
		$hostinfo = array();
		$error = false;
		if ($host !== false) {
			$config = $this->Dbconfig->fetch('news_traineds');
			$hostinfo = $config[$host];
			if (empty($hostinfo)) {
				$this->load->view('common/error',array('message' => $this->lang->line('news_train_host_not_found')));
				return;
			}
			$delete = $this->input->post('delete');
			if (isset($delete[$this->unicoder->base64_encode($host)])) {
				// deleting
				unset($config[$host]);
				$this->Dbconfig->save('news_traineds',$config);
				$this->_redirect('/admin/news/manage');
			}
		}
		$phost = $this->input->post('host');
		if (!empty($phost)) {
			$start = $this->input->post('start');
			$author_start = $this->input->post('author_start');
			if (empty($start)) {
				$error = $this->lang->line('news_train_not_empty');
			} else {
				// detect tag
				preg_match('/<(\w+)/',$start,$matches);
				if (empty($matches[1])) {
					$error = $this->lang->line('news_train_undetectable');
				} else {
					$tag = $matches[1];
				}
				$author_tag = false;
				if (!empty($author_start)) {
					preg_match('/<(\w+)/',$author_start,$matches);
					if (empty($matches[1])) {
						$error = $this->lang->line('news_train_author_undetectable');
					} else {
						$author_tag = $matches[1];
					}
				}

				if (empty($error)) {
					// look good
					$config = $this->Dbconfig->fetch('news_traineds'); 
					$config[$phost] = array(
						'start' => $start,
						'tag' => $tag,
						'author_start' => $author_start,
						'author_tag' => $author_tag,
						'entityencoded' => $this->input->post('entityencoded'),
						'alias' => $this->input->post('alias'),
					);
					$this->Dbconfig->save('news_traineds',$config);
					$this->_redirect('/admin/news/manage');
				}
			}
		}
		
		$this->load->view('news/admin_train',array(
			'host' => $host,
			'hostinfo' => $hostinfo,
			'error' => $error,
		));
	}
}
?>