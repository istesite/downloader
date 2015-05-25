<?php
# Youtube Class
date_default_timezone_set("Asia/Tehran");

class video {
	var $url = '';
	var $pageSourceCode = '';
	var $error = array();
	var $result = array();

	var $availableTypes = array(22, 18);


	function __construct($url = '') {
		if ($url != '') {
			$this->setUrl($url);

			for($i=0; $i<=20; $i++){
				$this->getVideoInfo();
				$data = $this->getResult();
				//echo "<pre>".var_export($data, true)."</pre>"; exit;
				if($data['type'] == 'video/mp4'){
					echo "<pre>".var_export($data, true)."</pre>"; exit;
				}
			}
		}
	}


	function __destruct() {
		if (count($this->error) > 0) {
			echo "Hata : \n<pre>" . var_export($this->error, TRUE) . "</pre>";
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


	function getVideoInfo(){
		//die('http://www.youtube.com/get_video_info?&video_id=' . $this->getVideoId() . '&asv=3&el=detailpage&hl=en_US');
		if($video_id = $this->getVideoId()){
			$this->curlGet('http://www.youtube.com/get_video_info?&video_id=' . $video_id . '&asv=3&el=detailpage&hl=en_US');

			$video_info = parse_url_data($this->pageSourceCode);
			$video_info2 = parse_url_data(urldecode($video_info['url_encoded_fmt_stream_map']));
			$video_infox = explode(',', urldecode($video_info['url_encoded_fmt_stream_map']));
			//echo "<pre>".var_export($video_infox, true)."</pre>"; exit;
			foreach($video_infox as $xxx){
				$video_data = parse_url_data(urldecode($xxx));
				$allVideoData[] = $video_data;

				if(in_array($video_data['itag'], $this->availableTypes)){
					$this->setResult('quality', $video_data['quality']);
					$this->setResult('fallback_host', $video_data['fallback_host']);
					$this->setResult('itag', $video_data['itag']);
					$this->setResult('url', $video_data['url']);
				}
			}


			$type = explode(';',urldecode($video_info2['type']));

			$urlData = parse_url_data(urldecode($video_info2['url']));


			$this->setResult('title', urldecode($video_info['title']));
			$this->setResult('author', urldecode($video_info['author']));
			$this->setResult('keywords', urldecode($video_info['keywords']));
			$this->setResult('picture_url', "http://i1.ytimg.com/vi/" . $video_id . "/hqdefault.jpg");

			$this->setResult('expire', date("G:i:s T", $urlData['expire']));
			$this->setResult('ipbits', $urlData['ipbits']);
			$this->setResult('ip', $urlData['ip']);
			$this->setResult('signature', $urlData['signature']);
			$this->setResult('types', $type);
			$this->setResult('typesss', $allVideoData);
			$this->setResult('type', $type[0]);
			$this->setResult('length', urldecode($video_info['length_seconds']));

			echo "<pre>".var_export($this->result, true)."</pre>"; exit;
		}
		else{
			return false;
		}

	}


	function getResult() {
		return $this->result;
	}


	function setUrl($urlx) {
		if ($urlx != '') {
			$this->url = $urlx;
		}
		else {
			$this->setError("Url bilgisi geçersiz.[" . $urlx . "]");
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
			return $tmp;
		}
	}
}