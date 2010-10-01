<?php
if (!function_exists('viname_xml_traverse')) {
	function viname_xml_traverse($key,$data,$indent = '') {
		if (is_object($data)) $data = get_object_vars($data); // turn it into an array
		
		if ($key !== null) echo "$indent<$key>";
		if (is_array($data)) {
			echo "\n";
			$non_numeric_found = false;
			foreach  (array_keys($data) as $ckey) {
				if (!is_numeric($ckey)) {
					$non_numeric_found = true;
					break;
				}
			}
			if ($non_numeric_found == false) {
				// change the key 
				foreach ($data as $ckey => $cvalue) {
					viname_xml_traverse($key . $ckey,$cvalue,"\t$indent");
				}
			} else {
				foreach ($data as $ckey => $cvalue) {
					viname_xml_traverse($ckey,$cvalue,"\t$indent");
				}
			}
			echo "$indent";
		} else {
			echo $data;
		}
		if ($key !== null) echo "</$key>\n";
	}
}
?>
<viname version="1.0" encoding="utf-8"?>
<?php viname_xml_traverse(null,$data) ?>
</viname>