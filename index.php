<?php
include_once "init.php";

//include_once "classes/youtube.class.php";
//include_once "classes/webtv.class.php";
include_once "classes/izlesene.class.php";

//$video = new video('https://www.youtube.com/watch?v=8NKKn2HGT2g');
//$video = new video('https://www.youtube.com/watch?v=GjMuvAHgx5E');
//$video = new video('https://www.youtube.com/watch?v=zTf-0T9BHJA&list=PLukqNtXBfGzahQEfJhcAn11DY98zhU1I1');
//$video = new video('http://cghb.web.tv/video/cok-guzel-hareketler-bunlar-70-bolum-alman-usulu__r9xkayai7ji');
//$video = new video('http://www.izlesene.com/video/silva-gunbardhi-ft-mandi-ft-dafi-te-ka-lali-shpirt/7146746');
$video = new video('http://www.izlesene.com/video/otilia-bilionera/7749333');

$url = "http://www.izlesene.com/video/otilia-bilionera/7749333";
$parser = detectDownloader($url);



echo "<pre>".var_export($video->getResult(), true)."</pre>";