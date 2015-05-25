<?php
error_reporting(false);

# PHP Error logları yeniden konumlandırılıyor.
ini_set('log_errors', 'Off');
ini_set('error_log', 'php.log');


define("DOWNLOAD_DIR", "files/");