<?php
error_reporting(true);

# PHP Error logları yeniden konumlandırılıyor.
ini_set('log_errors', 'On');
ini_set('error_log', './php.log');


define("DOWNLOAD_DIR", "files/");
define("DOWNLOAD_COUNTER", 5);
define("DOWNLOAD_FILE_MIN_SIZE", 2048);

define('DAILY_KEY', '60be796c494e722891e0');
define('DAILY_SECRET', '6d1ace84f3490385d29d0221b9b6ac2bea34acf1');
define('DAILY_USERNAME', 'manyetix');
define('DAILY_PASSWORD', '11259375');