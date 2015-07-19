<?php
include_once "init.php";
include_once "templates/form.html";

if(!isset($url) and isset($argv[1])){
	$url = $argv[1];
}

if(!isset($url) and isset($_REQUEST['url'])){
	$url = $_REQUEST['url'];
}

if(!isset($url) and isset($_REQUEST['video_url'])){
	$url = $_REQUEST['video_url'];
}

if(isset($_REQUEST['video_cat'])){
	$videoCategory = $_REQUEST['video_cat'];
}
else{
	$videoCategory = '';
}

if($url != '' and $db->numRowsCount("SELECT * FROM orjinal_videos WHERE orjinal_url='" . $url . "'") == 0){
	//writeFile('./downloadFileList.txt', $url);
	$parser = detectDownloader($url);

	echo "<h2>".strtoupper($parser)."</h2>\n";

	if(file_exists("classes/$parser.class.php")){
		include_once "classes/$parser.class.php";
	}

	$video = new video($url);
	if($parser == 'youtube' and $video->isPlaylist()){
		$playlist = $video->getPlaylistVideoId(true);
		foreach($playlist as $vidId){
			$videox = new video("https://www.youtube.com/watch?v=".$vidId);
			$vidData = $videox->getVideoInfo();
			echo "<div style='margin-bottom: 20px;'>
						<a href='https://www.youtube.com/watch?v=".$vidId."' target='_blank' style='float:left;'>
							<img src='http://i1.ytimg.com/vi/" . $vidId . "/hqdefault.jpg' style='height:120px; width:160px;' border:1px solid #333; />
						</a>
						<div style='height:120px; float:left;'>
							<span style='padding:10px;'>".$vidData['title']."</span><br>
							<a href='?exit&video_cat=".$videoCategory."&video_url=https://www.youtube.com/watch?v=".$vidId."' target='_blank' style='padding:10px; line-height:50px;'>Yükle</a>
						</div>
						<div style='clear:both;'></div>
					</div>\n";
			unset($videox);
		}
		exit;
	}
	else if($parser == 'youtube' and $video->isChannel()){
		$channellist = $video->getChannelVideoId(true);
		foreach($channellist as $vidId){
			$videox = new video("https://www.youtube.com/watch?v=".$vidId);
			$vidData = $videox->getVideoInfo();
			echo "<div style='margin-bottom: 20px;'>
						<a href='https://www.youtube.com/watch?v=".$vidId."' target='_blank' style='float:left;'>
							<img src='http://i1.ytimg.com/vi/" . $vidId . "/hqdefault.jpg' style='height:120px; width:160px;' border:1px solid #333; />
						</a>
						<div style='height:120px; float:left;'>
							<span style='padding:10px;'>".$vidData['title']."</span><br>
							<a href='?exit&video_cat=".$videoCategory."&video_url=https://www.youtube.com/watch?v=".$vidId."' target='_blank' style='padding:10px; line-height:50px;'>Yükle</a>
						</div>
						<div style='clear:both;'></div>
					</div>\n";
			unset($videox);
		}
		exit;
	}
	$data = $video->getResult();

	if($db->numRowsCount("SELECT * FROM videos WHERE title='".$db->clean($data['title'])."'") > 0){
		die('Bu isimde bir video zaten yüklenmiş.');
	}

	if($parser == 'youtube' and $data['length'] > 1000){
		$data['video_url'] = '';
	}

	if(urlExists($data['video_url'])){
		$downloadCounter = DOWNLOAD_COUNTER;
		$downloadStatus = false;
		while($downloadCounter){
			if($video->download()){
				echo "Dosya indirildi. OK <br>\n";
				echo "Dosya : " . DOWNLOAD_DIR . $data['video_file_name'] . " (" . byteCalc(filesize(DOWNLOAD_DIR . $data['video_file_name'])) . ")<br>\n";
				$downloadCounter = 0;
				$downloadStatus = true;
			}
			else{
				$downloadCounter--;
				sleep(1);
			}
		}
	}

	if($downloadStatus){
		try{
			require "includes/sdk/Dailymotion.php";

			$api = new Dailymotion();

			$api->setGrantType(
				Dailymotion::GRANT_TYPE_PASSWORD,
				DAILY_KEY,
				DAILY_SECRET,
				array('read', 'write', 'manage_videos'),
				array('username' => DAILY_USERNAME, 'password' => DAILY_PASSWORD)
			);


			/*
			//-> Generate tags
			$convertToLang = array('tr', 'en', 'fr');
			$currentLangDesc = langDetect($data['description']);
			$convText = array();
			foreach($convertToLang as $lngx){
				if($currentLangTitle != $lngx){
					$convText['title'][] = yandexCeviri($data['title'], $currentLangTitle, $lngx);
				}
			}
			foreach($convertToLang as $lngx){
				if($currentLangDesc != $lngx){
					$convText['desc'][] = yandexCeviri($data['title'], $currentLangDesc, $lngx);
				}
			}
*/
			$currentLangTitle = langDetect($data['title']);

			$vvii = realpath(DOWNLOAD_DIR . $data['video_file_name']);
			$urlx = $api->uploadFile($vvii);

			$videoPostData = array(
				'url'       => $urlx,
				'title'     => $data['title'],
				'tags'      => genVideoTag(genTranslateContent($data['title'], " ")),
				'description'=> genTranslateContent($data['description']),
				'channel'   => ($videoCategory!=''?$videoCategory:'webcam'),
				'language' => $currentLangTitle==false?'tr':$currentLangTitle,
				'published' => true,
			);
			$resultx = $api->post('/me/videos', $videoPostData);

			if(!$resultx){
				throw new Exception("<pre>".var_export($resultx, true)."</pre>");
			}

			if(isset($resultx['id']) and $resultx['id'] != ''){
				echo "<h5>".$resultx['title']."</h5>";
				echo "<pre>".var_export($videoPostData, true)."</pre>\n";
				echo '<h4 style="color:green;">YÜKLEME BAŞARILI! :)</h4>';

				$db->query("INSERT INTO orjinal_videos (orjinal_url, video_file, image_url, title, content, status, dailymotion_id, indate) VALUES ('".$url."', '".$data['video_file_name']."', '".$data['picture_url']."', '".$db->clean($data['title'])."', '".$db->clean($data['description'])."', '1', '".$resultx['id']."', '".time()."')");
				$lastInsertId = $db->insertId();

				$insertVideosSql = "INSERT INTO videos (dailymotion_id, title, descr, tags, lang, orj_id, indate, dailymotion_channel, duration) VALUES ('".$resultx['id']."', '".$db->clean($videoPostData['title'])."', '".$db->clean($videoPostData['description'])."', '".$db->clean($videoPostData['tags'])."', '".$videoPostData['language']."', '".$lastInsertId."','".time()."', '".DAILY_USERNAME."', '".(isset($data['length'])?$data['length']:'0')."')";
				//echo $insertVideosSql;
				$db->query($insertVideosSql);
			}
			else{
				echo '<h4 style="color:red;">VİDEO YÜKLENEMEDİ. :(</h4>';
			}

			if(file_exists(DOWNLOAD_DIR . $data['video_file_name'])){
				sleep(3);
				unlink(DOWNLOAD_DIR . $data['video_file_name']);
			}
		}catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	else{
		echo "<h4 style=\"color:red;\">Dosya indirilemedi. ERROR</h4> <br>\n";
	}

	if(isset($_REQUEST['exit'])){
		echo "<script>setTimeout(function(){ window.close(); }, 15000);</script>";
	}
}

