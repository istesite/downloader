<?php
# Youtube Class
date_default_timezone_set("Asia/Tehran");

class video {

	var $parserName = 'youtube';
	var $url = '';
	var $pageSourceCode = '';
	var $error = array();
	var $result = array();

	var $availableTypes = array(22, 18);


	function __construct($url = '') {
		if ($url != '') {
			$this->setUrl($url);
			$this->getVideoInfo();
		}
		else{
			$this->setError('Lütfen geçerli bir Youtube url giriniz.');
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
		if($video_id = $this->getVideoId()){
			$this->curlGet('http://www.youtube.com/get_video_info?&video_id=' . $video_id . '&asv=3&el=detailpage&hl=en_US');

			$video_info = parse_url_data($this->pageSourceCode);
			$video_info2 = parse_url_data(urldecode($video_info['url_encoded_fmt_stream_map']));
			$video_infox = explode(',', urldecode($video_info['url_encoded_fmt_stream_map']));

			foreach($video_infox as $xxx){
				$video_data = parse_url_data($xxx);

				if(in_array(urldecode($video_data['itag']), $this->availableTypes)){
					$this->setResult('quality', urldecode($video_data['quality']));
					$this->setResult('fallback_host', urldecode($video_data['fallback_host']));
					$this->setResult('itag', urldecode($video_data['itag']));
					$this->setResult('video_url', urldecode($video_data['url']));
					$types = explode(';', urldecode($video_data['type']));
					$this->setResult('type', $types[0]);
					break;
				}
			}

			$urlData = parse_url_data(urldecode($video_info2['url']));

			$this->setResult('title', urldecode($video_info['title']));
			$this->setResult('author', urldecode($video_info['author']));
			$this->setResult('keywords', urldecode($video_info['keywords']));
			$this->setResult('picture_url', "http://i1.ytimg.com/vi/" . $video_id . "/hqdefault.jpg");

			$this->setResult('expire', date("G:i:s T", $urlData['expire']));
			$this->setResult('ipbits', $urlData['ipbits']);
			$this->setResult('ip', $urlData['ip']);
			$this->setResult('signature', $urlData['signature']);
			$this->setResult('length', urldecode($video_info['length_seconds']));
			$this->setResult('video_file_name', $this->genVideoFileName());

			return $this->getResult();
		}
		else{
			return false;
		}

	}


	function getVideoId(){
		$data = parse_url($this->url);
		$data = parse_url_data($data['query']);

		if(isset($data['v']) and $data['v'] != ''){
			$this->setResult('video_id', $data['v']);
			return $data['v'];
		}
		else{
			$this->setError("video id değeri bulunamadı. [".$this->url."]");
			return false;
		}
	}


	function setError($errorMsg) {
		if ($errorMsg != '') {
			$this->error[] = $errorMsg;
		}
	}


	function curlGet($url) {
		$ch = curl_init();
		$timeout = 3;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		/* if you want to force to ipv6, uncomment the following line */
		//curl_setopt( $ch , CURLOPT_IPRESOLVE , 'CURLOPT_IPRESOLVE_V6');
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

	function download2() {
		$rh = fopen($this->result['video_url'], 'rb');
		$wh = fopen(DOWNLOAD_DIR.$this->result['video_file_name'], 'w+b');
		if (!$rh || !$wh) {
			return false;
		}

		while (!feof($rh)) {
			if (fwrite($wh, fread($rh, 4096)) === FALSE) {
				return false;
			}
			echo ' ';
			flush();
		}

		fclose($rh);
		fclose($wh);

		return true;
	}
}