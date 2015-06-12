<form method="POST" action="">
	<input type="text" name="video_url" size="50" style="height:40px; line-height:40px;" placeholder="http:// (daily., youtu., faceb., web.tv, izlesene.com, haberya.com.tr, ajanshaber.com, ...)">
	<select name="video_cat" style="height:40px; line-height:40px;">
		<option value="">Kategori...</option>
		<option value="school">Eğitim</option>
		<option value="news">Haber</option>
		<option value="animals">Hayvanlar</option>
		<option value="fun">Komedi ve Eğlence</option>
		<option value="people">Magazin</option>
		<option value="music">Müzik</option>
		<option value="auto">Oto-Moto</option>
		<option value="videogames">Oyun</option>
		<option value="creation">Sanat</option>
		<option value="travel">Seyahat</option>
		<option value="shortfilms">Sinema</option>
		<option value="sport">Spor</option>
		<option value="tv">TV & Dizi</option>
		<option value="tech">Teknoloji</option>
		<option value="webcam">Video Blog</option>
		<option value="lifestyle">Yaşam & Nasıl Yapılır</option>
	</select>
	<input type="submit" value="Yükle" style="height:40px; line-height:40px;" />
</form>
<div style="font-size: 10px; color:#999; line-height: 10px;">
	<p><b>Dailymotion:</b> http://www.dailymotion.com/video/x2tmwx0_new-best-club-dance-music-megamix-2015-club-music_music</p>
	<p><b>Youtube:</b> https://www.youtube.com/watch?v=2zNB56chXUA</p>
	<p><b>Facebook:</b> https://www.facebook.com/elcircodelamega/videos/10152785567701006/</p>
	<p><b>İzlesene:</b> http://www.izlesene.com/video/adem-gumuskaya-sansimiz-dondu/8552263</p>
	<p><b>Haber Ay:</b> http://www.haberay.com.tr/akraba-olsa-davetiye-verilmez-737v.htm</p>
	<p><b>Ajans Haber:</b> http://www.ajanshaber.com/bmwden-mercedese-gozdagi-video/199359</p>
	<p><b>Web.tv:</b> http://onuradiguzel.web.tv/video/hakan-durmazlar-feat-bbc-msne-gel__zwxwbjsiyqc</p>
</div>
<?php
include_once "init.php";


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

if($url != ''){
	writeFile('./downloadFileList.txt', $url);
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

	//echo "<pre>".var_export($data, true)."</pre>";
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


			//-> Generate tags
			$convertToLang = array('tr', 'en', 'fr', 'ja');
			$currentLangTitle = langDetect($data['title']);
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

			$vvii = realpath(DOWNLOAD_DIR . $data['video_file_name']);
			$urlx = $api->uploadFile($vvii);

			$videoPostData = array(
				'url'       => $urlx,
				'title'     => $data['title'],
				'tags'      => genVideoTag($data['title']),
				'description'=> $data['description'] . (count($convText['desc'])>0?"\r\n".implode("\r\n", $convText['desc']):''),
				'channel'   => ($videoCategory!=''?$videoCategory:'webcam'),
				'language' => $currentLangTitle,
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

