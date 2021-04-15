<?php

declare(strict_types=1);

namespace Flipsite\Exceptions;

use Exception;

final class AssetSourcesNotInstalledException extends Exception
{
    public function __construct(string $type, string $package)
    {
        $msg = "Asset source for '".$type."' is not installed. Install using `composer require ".$package."`";
        parent::__construct($msg);
    }
}
