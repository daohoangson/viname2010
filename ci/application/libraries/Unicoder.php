<?php

class Unicoder {
	public static function removeAccent($text) {
		static $map = array(
			'a' => array('à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ'),
			'e' => array('è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ'),
			'i' => array('ì','í','ị','ỉ','ĩ'),
			'o' => array('ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ'),
			'u' => array('ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ'),
			'y' => array('ỳ','ý','ỵ','ỷ','ỹ'),
			'd' => 'đ',
			'A' => array('À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ'),
			'E' => array('È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ'),
			'I' => array('Ì','Í','Ị','Ỉ','Ĩ'),
			'O' => array('Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ'),
			'U' => array('Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ'),
			'Y' => array('Ỳ','Ý','Ỵ','Ỷ','Ỹ'),
			'D' => 'Đ',
			'' => array('̉','̣','̃','̀','́'),
		);
		$result = $text;
		foreach ($map as $char => $chars) {
			$result = str_replace($chars,$char,$result);
		}
		return $result;
	}
	
	/**
	 * removes lowered case with accent translated to anscii characters
	 * safe to use with search functions
	**/
	public static function asciiAccent($text, $inLowerCase = false) {
		static $map = array(
			'à' => 'a`', 'á' => "a'", 'ạ' => 'a.', 'ả' => 'a?', 'ã' => 'a~', 
			'â' => 'a^', 'ầ' => 'a^`', 'ấ' => "a^'", 'ậ' => 'a^.', 'ẩ' => 'a^?', 'ẫ' => 'a^~',
			'ă' => 'a(', 'ằ' => 'a(`', 'ắ' => "a('", 'ặ' => 'a(.', 'ẳ' => 'a(?', 'ẵ' => 'a(~',
			'è' => 'e`', 'é' => "e'", 'ẹ' => 'e.', 'ẻ' => 'e?', 'ẽ' => 'e~',
			'ê' => 'e^', 'ề' => 'e^`', 'ế' => "e^'", 'ệ' => 'e^.', 'ể' => 'e^?', 'ễ' => 'e^~',
			'ì' => 'i`', 'í' => "i'", 'ị' => 'i.', 'ỉ' => 'i?', 'ĩ' => 'i~',
			'ò' => 'o`', 'ó' => "o'", 'ọ' => 'o.', 'ỏ' => 'o?', 'õ' => 'o~',
			'ô' => 'o^', 'ồ' => 'o^`', 'ố' => "o^'", 'ộ' => 'o^.', 'ổ' => 'o^?', 'ỗ' => 'o^~',
			'ơ' => 'o*', 'ờ' => 'o*`', 'ớ' => "o*'", 'ợ' => 'o*.', 'ở' => 'o*?', 'ỡ' => 'o*~',
			'ù' => 'u`', 'ú' => "u'", 'ụ' => 'u.', 'ủ' => 'u?', 'ũ' => 'u~',
			'ư' => 'u*', 'ừ' => 'u*`', 'ứ' => "u*'", 'ự' => 'u*.', 'ử' => 'u*?', 'ữ' => 'u*~',
			'ỳ' => 'y`', 'ý' => "y'", 'ỵ' => 'y.', 'ỷ' => 'y?', 'ỹ' => 'y~',
			'đ' => 'd-',
			'̉' => '?', '̣' => '.','̃' => '~','̀' => '`','́' => "'",
		);
		static $keys = array();
		if (empty($keys)) $keys = array_keys($map);
		static $marks = array('`',"'",'.','?','~');

		// convert characters
		$text = str_replace($keys,$map,($inLowerCase?$text:self::strtolower($text)));
		
		$words = explode(' ',$text);
		for ($i = 0; $i < count($words); $i++) {
			// count($words) penalty is low enough to just ignore it
			foreach ($marks as $mark) {
				$pos = strpos($words[$i],$mark);
				if ($pos !== false AND $pos < strlen($words[$i]) - 1) {
					// move it
					$words[$i] = substr($words[$i],0,$pos) . substr($words[$i],$pos + 1) . $mark;
				}
			}
		}
		
		return implode(' ',$words);
	}
	
	public static function strtolower($text, $reverse = false) {
		static $map = array(
			// lower case (67)
			'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
			'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
			'ì','í','ị','ỉ','ĩ',
			'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
			'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
			'ỳ','ý','ỵ','ỷ','ỹ',
			'đ',
			// upper case (67)
			'À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ',
			'È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ',
			'Ì','Í','Ị','Ỉ','Ĩ',
			'Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ',
			'Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ',
			'Ỳ','Ý','Ỵ','Ỷ','Ỹ',
			'Đ'
		);
		static $lower = array();
		static $upper = array();
		static $regex = null;
		
		if (empty($lower)) {
			$lower = array_slice($map,0,67);
			$upper = array_slice($map,67);
			for ($i = 65; $i <= 90; $i++) {
				// A = 65, a = 97, Z = 90
				$lower[] = chr($i + 32);
				$upper[] = chr($i);
			}
		}
		
		if (empty($reverse)) {
			$text = str_replace($upper,$lower,$text);
		} else {
			$text = str_replace($lower,$upper,$text);
		}
		
		return $text;
	}
	
	public static function strtoupper($text) {
		return self::strtolower($text,true);
	}
	
	public static function ucwords($text) {
		$words = explode(' ',self::strtolower($text));
		for ($i=0; $i < count($words); $i++) { 
			for ($j=0; $j < strlen($words[$i]); $j++) { 
				$sample = substr($words[$i],0,$j + 1);
				$changed = self::strtoupper($sample);
				if ($changed != $sample) {
					$words[$i] = $changed . substr($words[$i],$j + 1);
					break;
				}
			}
		}
		return implode(' ',$words);
	}
	
	public static function titleScore($title) {
		static $scores = array(
			array(
				'pho bo truong' => 9000,
				'bo truong' => 10000,
			),
			array(
				'pho chu tich' => 1400,
				'chu tich' => 1500,
			),
			array(
				'tong giam doc' => 1300,
				'tong gd' => 1300,
				'pho giam doc' => 900,
				'giam doc' => 1000,
				'pho gd' => 900,
				'gd' => 1000,
			),
			array(
				'truong phong' => 500,
				'pho phong' => 400,
				'truong ban' => 400,
				'pho ban' => 350,
			),
		);
		$title = strtolower(self::removeAccent($title));
		$parts = explode(',',$title);
		$score = 0;
		$score_parts = array();
		foreach ($parts as $part) {
			foreach ($scores as $scores_group) {
				foreach ($scores_group as $match => $score_plus) {
					if (strpos($title,$match) !== false) {
						$score += $score_plus;
						$score_parts[] = $match;
						break; // stop this scores_group
					}
				}
			}
		}
		return array($score,implode(',',$score_parts));
	}
	
	public static function base64_encode($text,$encoding = true) {
		static $search = array('+','/','='); // unsafe characters
		static $replace = array('.',':','_'); // somewhat safer ones
		if ($encoding) {
			return str_replace($search,$replace,base64_encode($text));
		} else {
			return base64_decode(str_replace($replace,$search,$text));
		}
	}
	
	public static function base64_decode($text) {
		return self::base64_encode($text,false);
	}
}