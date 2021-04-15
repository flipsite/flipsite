<?php

declare(strict_types=1);

namespace Flipsite\Exceptions;

use Exception;

final class ComponentNotFoundException extends Exception
{
    public array $data;

    public function __construct(string $component, $data)
    {
        $this->data = [$component => $data];
        parent::__construct('Component `'.$component.'` does not exist.');
    }
}
