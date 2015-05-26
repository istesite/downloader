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
	if(strstr($str, '?')){
		$expl = explode('?', $str);
		$str = $expl[1];
	}
	$data = explode('&', $str);
	$result = array();
	foreach($data as $val){
		$vals = explode('=', $val);

		$result[$vals[0]] = $vals[1];
	}
	return $result;
}

function parse_domain_url($str){
	$expl = explode('?', $str);
	$str = $expl[0];
	return $str;
}

function byteCalc($len){
	if($len < (1024)){
		$res = $len . " Byte";
	}
	else if($len < (1024 * 1024)){
		$res = round($len / (1024) , 1)." KB";
	}
	else if($len < (1024 * 1024 * 1024)){
		$res = round($len / (1024 * 1024) , 1)." MB";
	}
	else if($len < (1024 * 1024 * 1024 * 1024)){
		$res = round($len / (1024 * 1024 * 1024) , 1)." GB";
	}
	else if($len < (1024 * 1024 * 1024 * 1024 * 1024)){
		$res = round($len / (1024 * 1024 * 1024 * 1024) , 1)." TB";
	}
	else if($len < (1024 * 1024 * 1024 * 1024 * 1024 * 1024)){
		$res = round($len / (1024 * 1024 * 1024 * 1024 * 1024) , 1)." PB";
	}
	else{
		$res = round($len / (1024 * 1024 * 1024 * 1024 * 1024) , 1)." PB";
	}
	return $res;
}

function writeFile($dosya, $str){
	$baglan=@fopen ("$dosya",'a');
	fputs($baglan,$str."\n");
	fclose($baglan);
}

function urlExists($url = NULL){
	if($url == NULL) return array('success'=>false, 'descr'=>'Url yok', 'code'=>0);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.111 Safari/537.36');
	$data = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if($httpcode>=200 && $httpcode<300 or $httpcode == 302){
		return array('success'=>true, 'descr'=>'', 'code'=>$httpcode);
	} else {
		return array('success'=>false, 'descr'=>'', 'code'=>$httpcode);
	}
}

function csvToArray($data){
	$data = str_replace("\r", "", $data);
	$rows = explode("\n", $data);
	$returning = array();
	foreach($rows as $key=>$row){
		$cols = explode(",", $row);
		if(substr(trim($row),0,1) != '#' and trim($row) != 'domain,change,server' and trim($row) != ''){
			$returning[] = array('domainname'=>trim($cols[0]), 'change'=>trim($cols[1]), 'server'=>trim($cols[2]));
		}
	}
	return $returning;
}

function stripslashes_deep($value){
	if(isset($value)) {
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	}
	return $value;
}

function downloadx($source, $save, $referer=''){
	file_put_contents($save, fopen($source, 'r'));
	if(file_exists($save) and filesize($save) > 0){
		return true;
	}
	else{
		unlink($save);
		return false;
	}
}

function curlDownload($source, $save, $referer=''){
	$fp = fopen ($save, 'w+');
	$ch = curl_init(str_replace(" ", "%20", $source));
	curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.111 Safari/537.36');
	curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	if($referer != ''){
		curl_setopt($ch, CURLOPT_REFERER, $referer);
	}
	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

	curl_exec($ch); // get curl response
	curl_close($ch);
	fclose($fp);

	if(file_exists($save) and filesize($save) > 0){
		return true;
	}
	else{
		unlink($save);
		return false;
	}
}

function objectToArray($object)
{
	if(is_object($object))
	{
		$newArray = array();
		foreach((array)$object as $key => $val)
		{
			$newArray[$key] = $this->objectToArray($val);
		}
		return $newArray;
	}
	elseif(is_array($object))
	{
		$newArray = array();
		foreach($object as $key => $val)
		{
			$newArray[$key] = $this->objectToArray($val);
		}
		return $newArray;
	}
	else
		return $object;
}