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
		//echo $this->pageSourceCode; exit;
		$this->setResult('video_url', $this->getVideoUrl());
	}


	function getVideoId() {
		$data = parse_url($this->url);
		$data = parse_url_data($data['query']);

		if (isset($data['v']) and $data['v'] != '') {
			$this->setResult('video_id', $data['v']);

			return $data['v'];
		}
		else {
			$this->setError("video id değeri bulunamadı. [" . $this->url . "]");

			return FALSE;
		}
	}


	function getVideoUrl(){
		preg_match_all('/\[\["params".*?sd_src.*?(http.*?)"\]/si', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		return $result[1][0];
	}


	function getImageUrl(){
		preg_match_all('/<link rel="image_src" href="(.*?)">/', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$result = $result[1][0];
		return $result;
	}


	function getVideoName(){
		preg_match_all('%<meta itemprop="name" content="(.*?)" />%', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$result = $result[1][0];
		return $result;
	}


	function getVideoDesc(){
		preg_match_all('%<meta itemprop="description" content="(.*?)" />%', $this->pageSourceCode, $result, PREG_PATTERN_ORDER);
		$result = $result[1][0];
		$result = str_replace('...', '', $result);
		return $result;
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
		$fileName = '';
		if (isset($this->result['title']) and $this->result['title'] != '') {
			$fileName = $this->allowableChars($this->result['title']) . ".mp4";

			return $fileName;
		}
		else {
			return FALSE;
		}
	}


	function download() {
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
}
