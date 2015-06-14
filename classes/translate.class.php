<?php
class translate{
	var $APIKEY = 'trnsl.1.1.20150414T012912Z.76a3eaaf29fb8443.5a5eaf14b74ae29dc8e7df8ae117eeed47287c76';

	function langDetect($text){
		$url='https://translate.yandex.net/api/v1.5/tr.json/detect?key='.$this->APIKEY;
		$url.='&text='.rawurlencode($text);

		$result = json_decode($this->port($url));
		if($result->code == '200'){
			return $result->lang;
		}
		else{
			return false;
		}
	}

	function translator($text, $lang, $toLang){
		$url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key='.$this->APIKEY;
		$url .= '&lang='.$lang.'-'.$toLang;
		$url .= '&text='.rawurlencode($text);

		$result = json_decode($this->port($url));

		if($result->code == '200'){
			return $result->text[0];
		}
		else{
			return false;
		}
	}

	function port($url){
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

		return $html;
	}
}