<?php
ini_set('memory_limit', '-1');
include_once "./includes/conf.inc";
include_once "./includes/func.inc";
set_time_limit(0);
error_reporting(false);

if(!isset($date) and isset($argv[1])){
	$date = $argv[1];
}

if(!isset($date) and isset($_REQUEST['date'])){
	$date = $_REQUEST['date'];
}

if(!isset($date) and isset($_REQUEST['date'])){
	$date = $_REQUEST['date'];
}

$trensCountry = array("FR", "JP", "US", "IN", "TR");

function curlGet($url) {
	$useragent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.81 Safari/537.36';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, "https://www.google.com.tr/trends/hotvideos");
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	$tmp = curl_exec($ch);
	curl_close($ch);

	return $tmp;
}

$allVideoUrl = array();
echo "BASLAR\n";

if(file_exists("./trend_cron.log")){
	$fileLog = file_get_contents("./trend_cron.log");
	$fileLog = explode("\n", $fileLog);
	$fileLogs = array();
	foreach($fileLog as $logg){
		$fileLogs[] = trim($logg);
	}
	$fileLog = $fileLogs;
	unSet($fileLogs);
}
else{
	$fileLog = array();
}

echo "\n\nUPLOADER BASLAR\t".date("d-m-Y H:i:s")."\n";

foreach($trensCountry as $countryCode){
	$trends = json_decode(curlGet("https://www.google.com.tr/trends/hotvideos/hotItems?hvd&geo=".$countryCode."&mob=0&hvsm=1".((isset($date)&&$date!='')?"&hvd=".$date:'')));
	foreach($trends->videoList as $videoData){
		if(!in_array($videoData->url, $allVideoUrl) and !in_array($videoData->url, $fileLog)){
			echo $videoData->url."\n";
			$allVideoUrl[] = $videoData->url;
		}
	}
}

foreach($allVideoUrl as $vid){
	$result = curlGet("http://www.istesite.com/api/dailymotion/taka/?video_url=".$vid);
	if(strstr($result, "YÜKLEME BAŞARILI!")){
		echo $vid."\t - ".date("d-m-Y H:i:s")."\tOK\n";
		writeFile("./trend_cron.log", $vid);
	}
	else{
		echo $vid."\tERROR\n";
	}
	sleep(5);
}
echo "SON\n\n";