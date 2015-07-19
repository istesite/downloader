<?php
@header('Content-Type: text/html; charset=utf-8');

set_time_limit(1800);
ini_set('memory_limit', '-1');
error_reporting(false);

if(isset($_REQUEST['check_update'])){
	include_once "updater.php";
}

require_once "includes/conf.inc";
require_once "includes/api_conf.inc";
require_once "includes/func.inc";

error_reporting(false);

#Dizin Kontrolleri ve işlemleri
if (!file_exists(DOWNLOAD_DIR)) {
	mkdir(DOWNLOAD_DIR, 0777);
}
else if (!is_writable(DOWNLOAD_DIR)) {
	chmod(DOWNLOAD_DIR, 0777);
}

include_once "classes/dbSqlite.class.php";
$db = new dbSqlite(DB_NAME);
