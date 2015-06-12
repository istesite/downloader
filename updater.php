<?php
# Updater Version: 1.0
function download($source, $save){
	file_put_contents($save, fopen($source, 'r'));
	if(file_exists($save) and filesize($save) > 0){
		return true;
	}
	else{
		unlink($save);
		return false;
	}
}
if(!is_writable("./")){
	echo "Lütfen ".dirname("./")." dizinine 0777 yetkisi veriniz.";
}
if(download("http://www.manyetix.com/update.zip", "./update.zip")){
	$zip = new ZipArchive;
	if ($zip->open('./update.zip') === TRUE) {
		$zip->extractTo('./');
		$zip->close();
		//echo '<span style="color:green;">Update Success!</span>';
	} else {
		//echo '<span style="color:red;">Update Error!</span>';
	}
	unlink("./update.zip");
}
else{
	//echo '<span style="color:red;">Update Download Error!</span>';
}
header("Location:./");
