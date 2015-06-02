<form method="POST" action="">
	<input type="text" name="video_url" size="50" style="height:40px; line-height:40px;" placeholder="http:// (dailymotion, youtube, facebook, web.tv, izlesene.com, haberya.com.tr,...)">
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
<?php
include_once "init.php";

//$url = 'https://www.youtube.com/watch?v=8NKKn2HGT2g';
//$url = 'https://www.youtube.com/watch?v=GjMuvAHgx5E';
//$url = 'https://www.youtube.com/watch?v=zTf-0T9BHJA&list=PLukqNtXBfGzahQEfJhcAn11DY98zhU1I1';
//$url = 'http://cghb.web.tv/video/cok-guzel-hareketler-bunlar-70-bolum-alman-usulu__r9xkayai7ji';
//$url = 'http://www.izlesene.com/video/silva-gunbardhi-ft-mandi-ft-dafi-te-ka-lali-shpirt/7146746';
//$url = 'http://www.izlesene.com/video/hadise-prenses/7943261';
//$url = 'http://www.izlesene.com/video/otilia-bilionera/7749333';
//$url = 'https://www.facebook.com/manyetix/videos/909844355739944/?video_source=pages_finch_thumbnail_video';
//$url = 'https://www.facebook.com/manyetix/videos/vb.550724211651962/909844915739888/?type=2&theater';
//$url = 'http://www.dailymotion.com/video/x2rfpx9_kahkaha-atacaginiz-izleme-rekoru-kiran-en-komikler_fun';


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
	$parser = detectDownloader($url);
	echo "<h2>".strtoupper($parser)."</h2>\n";
	if(file_exists("classes/$parser.class.php")){
		include_once "classes/$parser.class.php";
	}

	$video = new video($url);
	$data = $video->getResult();

	echo "<pre>".var_export($data, true)."</pre>";
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
				//echo "$downloadCounter. Dosya indirilemedi. ERROR <br>\n";
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


			$vvii = realpath(DOWNLOAD_DIR . $data['video_file_name']);
			$urlx = $api->uploadFile($vvii);
			$resultx = $api->post('/me/videos',
				array(
					'url'       => $urlx,
					'title'     => $data['title'],
					'description'=> $data['description'],
					'channel'   => ($videoCategory!=''?$videoCategory:'webcam'),
					'published' => true,
				)
			);

			if(!$resultx){
				throw new Exception("<pre>".var_export($resultx, true)."</pre>");
			}

			if(isset($resultx['id']) and $resultx['id'] != ''){
				echo "<h5>".$resultx['title']."</h5>";
				echo '<h4 style="color:green;">YÜKLEME BAŞARILI! :)</h4>';
			}
			else{
				echo '<h4 style="color:red;">VİDEO YÜKLENEMEDİ. :(</h4>';
			}
			//echo "\n<pre>".var_export($resultx, true)."</pre>\n";

			if(file_exists(DOWNLOAD_DIR . $data['video_file_name'])){
				sleep(3);
				unlink(DOWNLOAD_DIR . $data['video_file_name']);
			}
		}catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	else{
		echo "Dosya indirilemedi. ERROR <br>\n";
	}
}

