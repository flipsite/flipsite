<?php

declare(strict_types=1);

namespace Flipsite\Assets\Options;

abstract class AbstractImageOption
{
    protected $value;

    public function __construct(protected string $prefix, protected bool $scalable = false)
    {
    }

    abstract public function getEncoded(?float $scale = null): ?string;

    abstract public function getValue();

    abstract public function parseValue(string $value): bool;

    abstract public function changeValue($value): void;
}
