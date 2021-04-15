<?php

declare(strict_types=1);

namespace Flipsite\Exceptions;

use Exception;

final class NoSiteFileFoundException extends Exception
{
    public function __construct(string $dir)
    {
        parent::__construct('No site.yaml or site.json found in CONTENT_DIR ('.$dir.')');
    }
}
