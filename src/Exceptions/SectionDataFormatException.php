<?php

declare(strict_types=1);

namespace Flipsite\Exceptions;

use Exception;

final class SectionDataFormatException extends Exception
{
    public array $data;
    public string $attribute;
    public $value;

    public function __construct(array $data, string $attribute, string $expectedFormat)
    {
        $this->data      = $data;
        $this->attribute = $attribute        ?? null;
        $this->value     = $data[$attribute] ?? null;
        $format          = isset($data[$attribute]) ? gettype($data[$attribute]) : 'missing';
        $msg             = 'Attribute `style:` in section data should be `'.$expectedFormat.'`, not `'.$format.'`';
        parent::__construct('Attribute `style:` in section data should be `'.$expectedFormat.'`, not `'.$format.'`');
    }
}
