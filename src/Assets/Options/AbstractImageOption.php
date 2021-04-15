<?php

declare(strict_types=1);

namespace Flipsite\Assets\Options;

abstract class AbstractImageOption
{
    protected string $prefix;
    protected bool $scalable;

    protected $value;

    public function __construct(string $prefix, bool $scalable = false)
    {
        $this->prefix   = $prefix;
        $this->scalable = $scalable;
    }

    abstract public function getEncoded(?float $scale = null) : ?string;

    abstract public function getValue();

    abstract public function parseValue(string $value) : bool;

    abstract public function changeValue($value) : void;
}
