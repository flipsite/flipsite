<?php

declare(strict_types=1);

namespace Flipsite\Exceptions;

use Exception;

final class AssetNotFoundException extends Exception
{
    public function __construct(string $asset)
    {
        $msg = 'Asset `'.$asset.'` not found';
        parent::__construct($msg);
    }
}
