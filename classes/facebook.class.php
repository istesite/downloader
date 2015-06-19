<?php
class video {

	var $parserName = 'facebook';
	var $url = '';
	var $pageSourceCode = '';
	var $error = array();
	var $result = array();


	function __construct($url = '') {
		if ($url != '') {
			$this->setUrl($url);
			$this->getVideoInfo();
		}
		else {
			$this->setError('Lütfen geçerli bir Facebook url giriniz.');
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
			$this->setResult('url', $urlx);
			$this->curlGet($this->url);
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


	function getVideoInfo() {
		$this->setResult('video_url', $this->getVideoUrl());
		$this->setResult('picture_url', $this->getImageUrl());
		$this->setResult('title', $this->getVideoName());
		$this->setResult('description', $this->getVideoDesc());
		$this->setResult('video_file_name', $this->genVideoFileName());
		return $this->getResult();
	}


	function getVideoUrl(){
		//die($this->pageSourceCode);
		if(strstr($this->pageSourceCode, 'This content is currently unavailable')){
			$this->setError("Login gerekli.");
		}
		preg_match_all('/(\[\["params".*?"\]\])/ix', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$data = $result[1][0];

		$data = json_decode($data);
		$data = urldecode($data[0][1]);

		preg_match_all('/sd_src_no_ratelimit":"(.*?)"/ix', $data, $result, PREG_PATTERN_ORDER);
		$data = $result[1][0];
		$data = stripslashes($data);

		return $data;
	}


	function getImageUrl(){
		preg_match_all('/background-image:[\s]{0,}url\(["|\'|s|]{0,}(.*?)["|\'|s|]{0,}\)/ix', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$result = stripslashes($result[1][0]);
		return $result;
	}


	function getVideoName(){
		preg_match_all('/\"hasCaption\"\>[\<br[\s]{0,}\/\>]{0,}(.*?)\</s', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		//echo "<pre>".var_export($result, true)."</pre>";
		$result = $result[1][0];
		return $result!=''?$result:'fb_'.date('d-m-Y H:i:s');
	}


	function getVideoDesc(){
		preg_match_all('/\"hasCaption\"\>[\<br[\s]{0,}\/\>]{0,}(.*?)\</s', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		//echo "<pre>".var_export($result, true)."</pre>";
		$result = $result[1][0];
		return $result!=''?$result:'fb_'.date('d-m-Y H:i:s');
	}


	function setError($errorMsg) {
		if ($errorMsg != '') {
			$this->error[] = $errorMsg;
		}
	}


	function curlGet($url) {
		$useragent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
		$referer = 'https://www.google.com/accounts/ServiceLogin?service=youtube';
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


	function allowableChars($str) {
		$charBad = array('ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü', ' ');
		$charGood = array('c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'O', 'S', 'U', '-');
		$str = str_replace($charBad, $charGood, $str);

		$find = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u',
		              'v', 'y', 'z', 'x', 'w', 'q',
		              'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U',
		              'V', 'Y', 'Z', 'X', 'W', 'Q',
		              '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '_', '-');

		$cleanStr = '';
		$strLen = strlen($str);

		for ($i = 0; $i < $strLen; $i++) {
			$char = substr($str, $i, 1);
			if (in_array($char, $find))
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


	function downloadx() {
		$source = $this->result['video_url'];
		$save = DOWNLOAD_DIR . $this->result['video_file_name'];
		$referer = $this->result['url'];

		file_put_contents($save, fopen($source, 'r'));
		if (file_exists($save) and filesize($save) > 2048) {
			return TRUE;
		}
		else {
			unlink($save);

			return FALSE;
		}
	}

	function downloadxx(){
		$source = $this->result['video_url'];
		$save = DOWNLOAD_DIR . $this->result['video_file_name'];

		$useragent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
		$referer = 'https://www.google.com/accounts/ServiceLogin?service=youtube';

		$fp = fopen ($save, 'w+');
		$ch = curl_init(str_replace(" ", "%20", $source));
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch,CURLOPT_USERAGENT,$useragent);
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


	function download(){
		$source = $this->result['video_url'];
		$save = DOWNLOAD_DIR . $this->result['video_file_name'];

		$fp = fopen ($save, 'w+');
		$ch = curl_init(str_replace(" ", "%20", $source));
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.111 Safari/537.36');
		curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		/*
		if($referer != ''){
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
		*/

		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

		curl_exec($ch); // get curl response
		curl_close($ch);
		fclose($fp);

		if(file_exists($save) and filesize($save) > DOWNLOAD_FILE_MIN_SIZE){
			return true;
		}
		else{
			unlink($save);
			return false;
		}
	}
}
