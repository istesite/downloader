<?php
function detectDownloader($url) {
	$result = '';
	if (strstr($url, 'youtube.com/watch') or strstr($url, 'youtu.be/')) {
		$result = 'youtube';
	}
	else if (strstr($url, 'web.tv/video/')) {
		$result = 'webtv';
	}
	else if (strstr($url, 'facebook.com/')) {
		$result = 'facebook';
	}
	else if (strstr($url, 'izlesene.com/')) {
		$result = 'izlesene';
	}
	else {
		$result = $_POST['video_site'];
	}

	return $result;
}

function parse_url_data($str){
	$data = explode('&', $str);
	$result = array();
	foreach($data as $val){
		$vals = explode('=', $val);

		$result[$vals[0]] = $vals[1];
	}
	return $result;
}