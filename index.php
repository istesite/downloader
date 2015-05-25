<?php
include_once "init.php";

//$url = 'https://www.youtube.com/watch?v=8NKKn2HGT2g';
//$url = 'https://www.youtube.com/watch?v=GjMuvAHgx5E';
//$url = 'https://www.youtube.com/watch?v=zTf-0T9BHJA&list=PLukqNtXBfGzahQEfJhcAn11DY98zhU1I1';
//$url = 'http://cghb.web.tv/video/cok-guzel-hareketler-bunlar-70-bolum-alman-usulu__r9xkayai7ji';
$url = 'http://www.izlesene.com/video/silva-gunbardhi-ft-mandi-ft-dafi-te-ka-lali-shpirt/7146746';
//$url = 'http://www.izlesene.com/video/otilia-bilionera/7749333';


$data = getVideoData($url);
echo "<pre>".var_export($data, true)."</pre>";
download($data['video_url'], DOWNLOAD_DIR.$data['video_file_name']);
echo byteCalc(filesize(DOWNLOAD_DIR.$data['video_file_name']));
