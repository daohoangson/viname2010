<?php

class Reader {
	private $verifyQueue = array();
	
	function init($config) {
		$this->traineds = empty($config['news_traineds'])?array():$config['news_traineds'];
		foreach (array_keys($this->traineds) as $host) {
			$hinfo =& $this->traineds[$host];
			if (!empty($hinfo['alias'])) {
				$alias = explode(',',$hinfo['alias']);
				foreach ($alias as $alias_single) {
					$this->traineds[trim($alias_single)] =& $hinfo;
				}
			}
		}
		$this->clean = empty($config['news_clean'])?array():$config['news_clean'];
		$this->ci =& get_instance();
		$this->ignores_sub = empty($config['news_ignores_sub'])?array():array_keys($config['news_ignores_sub']);
		$this->ignores = empty($config['news_ignores'])?array():array_keys($config['news_ignores']);
		$this->pronouns = empty($config['news_pronouns'])?array():array_keys($config['news_pronouns']);
	}
	
	function doFeed($url) {
		$feed = file_get_contents($url);
		
		if (!empty($feed)) {
			$parsed = parse_url($url);
			$prefix = "$parsed[scheme]://$parsed[host]" . str_replace('\\','/',dirname($parsed['path']));
			$feed = preg_replace('/href=("|\')?\//','href="' . $prefix,$feed);
			$results = array();
			foreach (array_keys($this->traineds) as $host) {
				$regex = '/' . preg_quote('http://' . $host . '/','/') . '[a-z0-9\/\-\.\%]+/i';
				$offset = 0;
				while (preg_match($regex,$feed,$matches,PREG_OFFSET_CAPTURE,$offset)) {
					$news_url = $matches[0][0];
					$offset = $matches[0][1] + strlen($news_url);
					$ext = strtolower(substr($news_url,-4));
					if (!in_array($ext,array('jpeg','.jpg','.png','.gif'))) {
						$results[] = $this->read($news_url,false);
						$this->_verifyQueue($results[count($results) - 1]);
					}
				}
			}
			$this->_verifyProcess();
		}
		
		return $results;
	}
	
	function read($url,$verify = true) {
		set_time_limit(5); // each url shouldn't take more than 5 seconds
		$url_parsed = parse_url($url);
		$result = array(
			'url' => $url,
			'names' => array(),
			'error' => false,
		);
		if (isset($this->traineds[$url_parsed['host']])) {
			$result['host'] = $url_parsed['host'];
			$trained =& $this->traineds[$url_parsed['host']]; // keep the data somewhere close
			$content = file_get_contents($url); // read url
			
			list($pos,$pos_close) = $this->extract($content,$trained['start'],$trained['tag'],true,$result);

			if ($pos !== false AND $pos_close > $pos) {
				// look like a correct content
				// start extracting the found content
				$primary_content = substr($content,$pos,$pos_close - $pos);
				if ($trained['author_start']) {
					// try to strip off author info now
					list($author_pos,$author_pos_close) = $this->extract($primary_content,$trained['author_start'],$trained['author_tag'],false);
					if ($author_pos !== false AND $author_pos_close > $author_pos) {
						$primary_content = substr($primary_content,0,$author_pos) . substr($primary_content,$author_pos_close);
					}
				}
				$primary_content = strip_tags($primary_content);
				if ($trained['entityencoded']) {
					$primary_content = html_entity_decode ($primary_content, ENT_COMPAT, 'UTF-8');
				}
				$primary_content = str_replace($this->clean,'.',$primary_content);
				$primary_content = str_replace(',','.',$primary_content);
				$primary_content = preg_replace('/[\r\n]+/','.',$primary_content);
				unset($content); // free memory
				// process content
				$sentences = explode('.',$primary_content); // extract as sentences
				unset($primary_content); // free memory
				foreach ($sentences as $sentence) {
					// sentence by sentence
					$current = array();
					$words = explode(' ',$sentence);
					$count = count($words);
					for ($i = 0; $i <= $count; $i++) {
						if ($i === $count) $words[$i] = 'a'; // we want to process the last word correctly
						$words[$i] = trim($words[$i]);
						if (strlen($words[$i]) == 0) continue;
						if (preg_match('/[0-9]/',$words[$i])) $words[$i] = 'a'; // ignore words with numbers
						
						if ($this->ci->unicoder->ucwords($words[$i]) == $words[$i]) {
							// use the ucwords (unicode version) to check if the word has correct case
							// it has now, add it to queue
							$ascii = $this->ci->unicoder->asciiAccent($words[$i]);
							if (!in_array($ascii,$this->pronouns)) {
								// treat pronoun as normal word
								$current[$ascii] = $words[$i];
								continue;
							} 
						}
						
						// we found a normal word...
						if (count($current) >= 2) {
							// more than 2 words in queue
							// process it now
							$implode = implode(' ',$current);
							$ascii = implode(' ',array_keys($current));
							$ignoreThis = false;
							if (in_array($ascii,$this->ignores)) {
								// matched the ignores array
								$ignoreThis = true;
							} else {
								foreach ($this->ignores_sub as $substr) {
									if (strpos($ascii,$substr) !== false) {
										// matched a substr
										$ignoreThis = true;
										break; // stop the loop
									}
								}
							}
							if (!$ignoreThis) $result['names'][$ascii] = $implode;
						}
						$current = array(); // reset queue
					}
				}

				$result['names'] = array_unique($result['names']);
				if ($verify) {
					$this->verify($result);
				}
			}
		} else {
			$result['error'] = 'UNTRAINED';
		}
		return $result;
	}
	
	function extract($content,$start,$tag,$direction,&$result = array()) {
		if ($direction) {
			$pos = stripos($content,$start); // look for the start HTML
		} else {
			$pos = strripos($content,$start); // look for the start HTML, backward
		}

		$pos_close = false;
		$tag_open = '<' . $tag;
		$tag_close = '</' . $tag . '>';
		$attempts = 0;
		
		if ($pos === false) {
			$result['error'] = 'START_NOT_FOUND';
		} else {
			$count = 1;
			$current_pos = $pos + 1;
			do {
				$attempts++;
				if ($attempts > 200) {
					// too many attempts 
					$result['error'] = 'FUCK ANYONE WHO PUBLISHED THAT PIECE OF SHIT';
					break;
				}
				// look for the correct close tag 
				$pos_open = stripos($content,$tag_open,$current_pos);
				$pos_close = stripos($content,$tag_close,$current_pos);

				if ($pos_close === false) {
					$result['error'] = 'TAG_MISMATCHED';
					break;
				}
				if ($pos_open === false) $pos_open = strlen($content);
				if ($pos_open < $pos_close) {
					$count++;
					$current_pos = $pos_open + 1;
				} else {
					$count--;
					$current_pos = $pos_close + 1;
				}
			} while ($count > 0);
		}
		
		if ($pos_close !== false) $pos_close += strlen($tag_close);
		
		return array($pos,$pos_close);
	}
	
	function verify(&$result) {
		$this->_verifyQueue($result);
		$this->_verifyProcess();
	}
	
	private function _verifyQueue(&$result) {
		foreach (array_keys($result['names']) as $key) {
			$words = explode(' ',$key);
			$name = $words[count($words) - 1]; // get the ascii version of name
			$this->verifyQueue[$name][] = &$result['names'][$key];
		}
	}
	
	private function _verifyProcess() {
		$CI =& get_instance();
		$names = array_keys($this->verifyQueue);
		if (empty($names)) return;
		$names_safe = array_map(array($CI->db,'escape'),$names);
		$query = $CI->db->query("
			SELECT `name_ascii`,`count`
			FROM `" . $CI->db->dbprefix('index_names') . "`
			WHERE `name_ascii` IN (" . implode(",",$names_safe) . ")
		");
		$founds = $query->result();
		$notfounds = array_combine($names,array_fill(0,count($names),1));
		foreach ($founds as $found) {
			unset($notfounds[$found->name_ascii]);
		}
		foreach (array_keys($notfounds) as $notfound) {
			foreach (array_keys($this->verifyQueue[$notfound]) as $key) {
				$this->verifyQueue[$notfound][$key] = null;
			}
		}
		$this->verifyQueue = array(); // reset 
	}
}

?>