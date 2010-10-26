<?php

function extractYearFromMysqlDate($dob,$default = '') {
	$parts = explode('-',$dob);
	if (count($parts) == 3) {
		$year = intval($parts[0]);
		if ($year > 0) {
			return $year;
		} else {
			return $default;
		}
	} else {
		return $default;
	}
}

function parseTitleAndInfo($title,$data) {
	$return = array();
	if (!empty($title)) $return[] = $title; else $return[] = false;
	$parts = explode('/',$data);
	if (count($parts) > 3) {
		$info = implode('/',array_slice($parts,3));
		$return[] = $info;
	} else {
		$return[] = false;
	}
	return $return;
}