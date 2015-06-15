<?php
ini_set('memory_limit', '-1');
include_once "./includes/conf.inc";
include_once "./includes/func.inc";
set_time_limit(0);
error_reporting(false);

$trensCountry = array("FR", "JP", "TR");

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
echo "Başlar\n";

foreach($trensCountry as $countryCode){
	//echo "$countryCode\n";
	$trends = json_decode(curlGet("https://www.google.com.tr/trends/hotvideos/hotItems?hvd&geo=".$countryCode."&mob=0&hvsm=1"));
	//echo $trends->videoList[0]->url; exit;
	//echo "<pre>".var_export($trends->videoList, true)."</pre>";
	foreach($trends->videoList as $videoData){
		if(!in_array($videoData->url, $allVideoUrl)){
			echo $videoData->url."\n";
			$allVideoUrl[] = $videoData->url;
		}
	}
}

foreach($allVideoUrl as $vid){
	$result = curlGet("http://www.istesite.com/api/dailymotion/taka/?video_url=".$vid);
	if(strstr($result, "YÜKLEME BAŞARILI!")){
		echo $vid."\tOK\n";
	}
	else{
		echo $vid."\tERROR\n";
	}
	sleep(5);
}
echo "SON";