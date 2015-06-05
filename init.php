<?php
set_time_limit(1800);
ini_set('memory_limit', '-1');
require_once "includes/conf.inc";
require_once "includes/func.inc";

#Dizin Kontrolleri ve işlemleri
if (!file_exists(DOWNLOAD_DIR)) {
	mkdir(DOWNLOAD_DIR, 0777);
}
else if (!is_writable(DOWNLOAD_DIR)) {
	chmod(DOWNLOAD_DIR, 0777);
}