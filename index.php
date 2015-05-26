<?php
include_once "init.php";

//$url = 'https://www.youtube.com/watch?v=8NKKn2HGT2g';
//$url = 'https://www.youtube.com/watch?v=GjMuvAHgx5E';
//$url = 'https://www.youtube.com/watch?v=zTf-0T9BHJA&list=PLukqNtXBfGzahQEfJhcAn11DY98zhU1I1';
//$url = 'http://cghb.web.tv/video/cok-guzel-hareketler-bunlar-70-bolum-alman-usulu__r9xkayai7ji';
//$url = 'http://www.izlesene.com/video/silva-gunbardhi-ft-mandi-ft-dafi-te-ka-lali-shpirt/7146746';
$url = 'http://www.izlesene.com/video/hadise-prenses/7943261';
//$url = 'http://www.izlesene.com/video/otilia-bilionera/7749333';
//$url = 'https://www.facebook.com/manyetix/videos/909844355739944/?video_source=pages_finch_thumbnail_video';
//$url = 'https://www.facebook.com/manyetix/videos/vb.550724211651962/909844915739888/?type=2&theater';

if(!isset($url) and isset($argv[1])){
	$url = $argv[1];
}

if(!isset($url) and isset($_REQUEST['url'])){
	$url = $_REQUEST['url'];
}


$parser = detectDownloader($url);
echo $parser."\n";
if(file_exists("classes/$parser.class.php")){
	include_once "classes/$parser.class.php";
}

$video = new video($url);
$data = $video->getResult();

echo "<pre>".var_export($data, true)."</pre>";
if(urlExists($data['video_url'])){
	$downloadCounter = DOWNLOAD_COUNTER;
	while($downloadCounter){
		if($video->download()){
			echo "Dosya indirildi. OK \n";
			echo "Dosya : " . DOWNLOAD_DIR . $data['video_file_name'] . " (" . byteCalc(filesize(DOWNLOAD_DIR . $data['video_file_name'])) . ")\n";
			$downloadCounter = 0;
		}
		else{
			echo "$downloadCounter. Dosya indirilemedi. ERROR \n";
			$downloadCounter--;
			sleep(1);
		}
	}
}
