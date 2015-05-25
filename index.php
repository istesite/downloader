<?php
include_once "init.php";
echo "<pre>".var_export($argv, true)."</pre>";
if(isset($argv[1])){
	$url = $argv[1];
}

//$url = 'https://www.youtube.com/watch?v=8NKKn2HGT2g';
//$url = 'https://www.youtube.com/watch?v=GjMuvAHgx5E';
//$url = 'https://www.youtube.com/watch?v=zTf-0T9BHJA&list=PLukqNtXBfGzahQEfJhcAn11DY98zhU1I1';
//$url = 'http://cghb.web.tv/video/cok-guzel-hareketler-bunlar-70-bolum-alman-usulu__r9xkayai7ji';
//$url = 'http://www.izlesene.com/video/silva-gunbardhi-ft-mandi-ft-dafi-te-ka-lali-shpirt/7146746';
//$url = 'http://www.izlesene.com/video/otilia-bilionera/7749333';


$parser = detectDownloader($url);
if(file_exists("classes/$parser.class.php")){
	include_once "classes/$parser.class.php";
}

$video = new video($url);
$data = $video->getResult();

echo "<pre>".var_export($data, true)."</pre>";
if(urlExists($data['video_url'])){
	if($video->download()){
		echo "dosya indirildi.";
		echo byteCalc(filesize(DOWNLOAD_DIR.$data['video_file_name']));
	}
	else{
		echo "dosya indirilemedi.";
	}
}
