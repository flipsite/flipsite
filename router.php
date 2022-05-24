<?php

$pathinfo = pathinfo(__DIR__.$_SERVER['REQUEST_URI']);
if (isset($pathinfo['extension']) && file_exists(__DIR__.$_SERVER['REQUEST_URI'])) {
    return false;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF']    = '/index.php';
putenv('APP_ENV=localhost');
putenv('SITE_DIR='.__DIR__.'/../../..');
putenv('VENDOR_DIR='.__DIR__.'/../..');
putenv('IMG_DIR='.__DIR__.'/img');
putenv('VIDEO_DIR='.__DIR__.'/videos');
require_once getenv('VENDOR_DIR').'/flipsite/flipsite/src/index.php';
