<?php

declare(strict_types=1);

namespace Flipsite\Assets\Options;

final class BoolOption extends AbstractImageOption
{
    public function getEncoded(?float $scale = null) : ?string
    {
        return $this->value ? $this->prefix : null;
    }

    public function getValue()
    {
        return $this->value ? true : false;
    }

    public function changeValue($value) : void
    {
        $this->value = (bool) $value;
    }

    public function parseValue(string $value) : bool
    {
        if ($value === $this->prefix) {
            $this->value = true;
            return true;
        }
        return false;
    }
}
