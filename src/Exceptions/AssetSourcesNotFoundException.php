<?php

declare(strict_types=1);

namespace Flipsite\Exceptions;

use Exception;

final class AssetSourcesNotFoundException extends Exception
{
    public function __construct(string $type, string $class)
    {
        $msg = "Asset source for '".$type."' not found (".$class.")'";
        parent::__construct($msg);
    }
}
