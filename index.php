<?php

putenv('APP_ENV=localhost');
putenv('SITE_DIR='.__DIR__.'/../../..');
putenv('VENDOR_DIR='.__DIR__.'/../..');
putenv('IMG_DIR='.__DIR__.'/img');
putenv('VIDEO_DIR='.__DIR__.'/videos');
require_once getenv('VENDOR_DIR').'/flipsite/flipsite/src/index.php';
