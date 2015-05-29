<?php
class video {

	var $parserName = 'webtv';
	var $url = '';
	var $pageSourceCode = '';
	var $error = array();
	var $result = array();

	function __construct($url = '') {
		if ($url != '') {
			$this->setUrl($url);
			$this->getVideoInfo();
		}
		else{
			$this->setError('Lütfen geçerli bir "haberay.com.tr" url giriniz.');
		}
	}


	function __destruct() {
		if (count($this->error) > 0) {
			$this->setResult('error', $this->error);
		}
	}


	function setUrl($urlx) {
		if ($urlx != '') {
			$this->url = $urlx;
			$this->pageSourceCode = $this->curlGet($urlx);
			$this->setResult('url', $urlx);
		}
		else {
			$this->setError("Url bilgisi geçersiz.[" . $urlx . "]");
		}
	}


	function setResult($key, $value) {
		if (!isset($this->result[$key])) {
			$this->result[$key] = $value;
		}
		else {
			self::setError($key . ": daha önce tanımlanmış.");
		}
	}


	function getResult() {
		return $this->result;
	}


	function getVideoInfo(){
		$this->setResult('video_url', $this->getVideoUrl());
		$this->setResult('picture_url', $this->getImageUrl());
		$this->setResult('title', $this->getVideoName());
		$this->setResult('description', $this->getVideoDesc());
		$this->setResult('video_file_name', $this->genVideoFileName());
		return $this->getResult();
	}


	function getVideoUrl(){
		preg_match_all('/\<div[\s\n\r\t]{0,}class=[\"\']{1,}code[\"\']{1,}\>[\s\n\r\t]{0,}\<iframe.*?src=[\"\']{1,}(.*?)\/index.html[\"\']{1,}.*?\>/', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$urlFile = explode('/', $result[1][0]);
		$urlFile = $urlFile[count($urlFile) - 1];
		$videoUrl = $result[1][0]."/".$urlFile."-360p.mp4";
		//die($videoUrl)
		return $videoUrl;
	}


	function getImageUrl(){
		preg_match_all('/\<link[\s\n\r\t]{0,}rel=[\"\']{1,}image_src[\"\']{1,}[\s\n\r\t]{0,}type=[\"\']{1,}image/jpeg[\"\']{1,}[\s\n\r\t]{0,}href=[\"\']{1,}(.*?)[\"\']{1,}[\s\n\r\t]{0,}\/\>/', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$result = $result[1][0];
		return $result;
	}


	function getVideoName(){
		preg_match_all('%\<title\>(.*?)\<\/title\>%', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$result = $result[1][0];
		return $result;
	}


	function getVideoDesc(){
		preg_match_all('%\<meta[\s\n\r\t]{0,}name=[\"\']{1,}description[\"\']{1,}[\s\n\r\t]{0,}content=[\"\']{1,}(.*?)[\"\']{1,}[\s\n\r\t]{0,}\/\>%', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$result = $result[1][0];
		$result = str_replace('...', '', $result);
		return $result;
	}


	function curlGet($url) {
		$useragent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.111 Safari/537.36';
		$referer = $this->url;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		$tmp = curl_exec($ch);
		curl_close($ch);

		if ($tmp == FALSE or $tmp == '') {
			if ($tmp == FALSE) {
				$this->setError('curlError: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
			}
			else {
				$this->setError("Curl ile sayfa içeriği alınmadı.");
			}
		}
		else {
			$this->pageSourceCode = $tmp;
		}

		return $tmp;
	}


	function setError($errorMsg) {
		if ($errorMsg != '') {
			$this->error[] = $errorMsg;
		}
	}


	function allowableChars($str) {
		$charBad = array('ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü', ' ');
		$charGood = array('c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'O', 'S', 'U', '-');
		$str = str_replace($charBad, $charGood, $str);

		$find = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','r','s','t','u','v','y','z','x','w','q',
		              'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','R','S','T','U','V','Y','Z','X','W','Q',
		              '0','1','2','3','4','5','6','7','8','9','_','-');

		$cleanStr = '';
		$strLen = strlen($str);

		for($i=0; $i<$strLen; $i++)
		{
			$char = substr($str, $i, 1);
			if(in_array($char, $find))
				$cleanStr .= $char;
		}

		return $cleanStr;
	}


	function genVideoFileName() {
		$fileName = $this->parserName . "_" . date('YmdHis') . ".mp4";

		if (isset($this->result['title']) and $this->result['title'] != '') {
			$fileName = $this->allowableChars($this->result['title']) . ".mp4";
		}

		return $fileName;
	}


	function download(){
		$source = $this->result['video_url'];
		$save = DOWNLOAD_DIR.$this->result['video_file_name'];
		$referer = $this->result['url'];

		file_put_contents($save, fopen($source, 'r'));
		if(file_exists($save) and filesize($save) > DOWNLOAD_FILE_MIN_SIZE){
			return true;
		}
		else{
			unlink($save);
			return false;
		}
	}
}