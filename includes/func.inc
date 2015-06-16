<?php
function detectDownloader($url) {
	$result = '';
	if (strstr($url, 'youtube.com/') or strstr($url, 'youtu.be/')) {
		$result = 'youtube';
	}
	else if (strstr($url, 'facebook.com/')) {
		$result = 'facebook';
	}
	else if (strstr($url, 'web.tv/')) {
		$result = 'webtv';
	}
	else if (strstr($url, 'izlesene.com/')) {
		$result = 'izlesene';
	}
	else if (strstr($url, 'haberay.com.tr/')) {
		$result = 'haberay';
	}
	else if (strstr($url, 'dailymotion.com/')) {
		$result = 'dailymotion';
	}
	else if (strstr($url, 'ajanshaber.com/')) {
		$result = 'ajanshaber';
	}
	else if (strstr($url, 'mynet.com/')) {
		$result = 'mynet';
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


function csvParse($str, $spliter = ";", $firstLineTitle = false){
	$result = array();
	$str = str_replace("\r", "", $str);
	$rows = explode("\n", $str);
	foreach($rows as $row){
		$cols = explode($spliter, $row);
		if($firstLineTitle and count($result) == 0){
			$title = $cols;
			continue;
		}

		foreach($cols as $colKey=>$colVal){
			if(isset($title) and count($title) > 0){
				$result[][$title[$colKey]] = $colVal;
			}
			else{
				$result[] = $colVal;
			}
		}
	}

	return $result;
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

function yandexCeviri($text, $from, $to){
	$url='http://ceviri.yandex.net/api/v1/tr.json/detect?callback=ya_.json.c(3)&lang=';
	$url.=$from."-".$to;
	$url.='&text='.rawurlencode($text);
	$url.='&srv=tr-text&id=9c7c13c5-0-0&reason=paste&options=4';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch, CURLOPT_URL, $url);
	$html = curl_exec($ch);
	curl_close($ch);
	preg_match_all("#\"(.*?)\"#si",$html,$s);
	return str_replace('"',"",$s[0][6]);
}

function langDetect($text){
	$apiKey = 'trnsl.1.1.20150605T150916Z.19dc791723fe845e.a993aadc68df578c002401987a347c9dcdadd7fb';
	$url='https://translate.yandex.net/api/v1.5/tr.json/detect?key='.$apiKey;
	$url.='&text='.rawurlencode($text);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
	curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch, CURLOPT_URL, $url);
	$html = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($html);
	if($result->code == '200'){
		return $result->lang;
	}
	else{
		return false;
	}
}

function genVideoTag($str){
	$bad = array('the', 'and', 'for', 'yet', 'but', 'nor', 'and',
	             "açıkçası ", "ama ", "ancak ", "bile ", "çünkü", "dahi ", "demek", "fakat", "gene", "gerek", "hâlbuki", "hatta", "hele", "hem", "hem de ", "ile", "ise", "ister", "kâh", "kısacası", "lâkin ", "madem", "nitekim", "oysa ", "oysaki ", "öyle ki", "öyleyse ", "üstelik ", "veya", "veyahut", "yahut", "yalnız", "yine", "yoksa", "zira");
	$str = urldecode($str);
	$str = strtolower($str);
	$str = str_replace(array(',', '.', ';', ':', '!', '?', '-', '_', '"', "'"), '', $str);
	$str = str_replace(array("(", ")", "[","]", "{","}", "/", "\\"), ' ', $str);
	$words = explode(' ', $str);
	$result = array();
	foreach($words as $word){
		if(strlen($word)>= 3 and !in_array($word, $bad) and !in_array($word, $result) and trim($word) != ''){
			$result[] = $word;
		}
	}
	//return json_encode($result);
	return custom_json_encode($result);
}

function custom_json_encode($array){
	$str = '';
	foreach($array as $arr){
		if($str == ''){
			$str .= '"'.$arr.'"';
		}
		else{
			$str .= ',"'.$arr.'"';
		}
	}
	return '['.$str.']';
}

function getTwitterTags($str){
	preg_match_all('/<meta[\s\n\t\r]{1,}name="twitter:([title|description|url|image]{1,})"[\s\n\t\r]{1,}content="(.*?)">/sx', $str, $result, PREG_PATTERN_ORDER);
	$res = array();
	foreach($result[1] as $key=>$val){
		$res[$val] = $result[2][$key];
	}
	return $res;
}

function getFacebookTags($str){
	preg_match_all('/<meta[\s\n\t\r]{1,}property="og:([title|description|url|image]{1,})"[\s\n\t\r]{1,}content="(.*?)">/sx', $str, $result, PREG_PATTERN_ORDER);
	$res = array();
	foreach($result[1] as $key=>$val){
		$res[$val] = $result[2][$key];
	}
	return $res;
}

function genTranslateContent($str, $spliter="\r\n", $langs = array("tr", "en", "de")){
	include_once "classes/translate.class.php";
	$transStr = array();
	$trans = new translate();
	if(!isset($langs)){
		$transLangs = array("tr", "en", "de");
	}
	else{
		$transLangs = $langs;
	}
	$currLang = $trans->langDetect($str);

	foreach($transLangs as $lngx){
		if($currLang == $lngx){
			$transStr[$lngx] = $str;
		}
		else{
			$transStr[$lngx] = $trans->translator($str, $currLang, $lngx);
		}
	}
	//echo "<pre>".var_export($transStr, true)."</pre>";
	return implode($spliter, $transStr);
}