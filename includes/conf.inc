<?php
# PHP Error logları yeniden konumlandırılıyor.
ini_set('log_errors', 'On');
ini_set('error_log', './php.log');


define("DOWNLOAD_DIR", "files/");
define("DOWNLOAD_COUNTER", 5);
define("DOWNLOAD_FILE_MIN_SIZE", 2048);
define("DB_TYPE", "SQLITE");
define("DB_NAME", "database");
