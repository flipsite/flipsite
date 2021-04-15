<?php

declare(strict_types=1);

namespace Flipsite\Exceptions;

use Exception;

final class AssetNotFoundException extends Exception
{
    public function __construct(string $asset, string $filename)
    {
        $msg = 'Asset `'.$asset.'` not found folder `'.str_replace('/'.$asset, '', $filename).'`';
        parent::__construct($msg);
    }
}
