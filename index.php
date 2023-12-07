<?php

putenv('APP_ENV=localhost');
putenv('SITE_DIR='.__DIR__.'/../../..');
putenv('VENDOR_DIR='.__DIR__.'/../..');
putenv('CACHE_DIR='.__DIR__.'/img');
require_once getenv('VENDOR_DIR').'/flipsite/flipsite/src/index.php';
