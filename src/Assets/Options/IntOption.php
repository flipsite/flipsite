<?php

declare(strict_types=1);

namespace Flipsite\Assets\Options;

final class IntOption extends AbstractImageOption
{
    public function getEncoded(?float $scale = null) : ?string
    {
        if (null === $this->value) {
            return null;
        }
        if ($this->scalable && $scale) {
            $value = round($this->value * $scale, 0);
        } else {
            $value = round($this->value, 0);
        }
        return $this->prefix.$value;
    }

    public function getValue()
    {
        return null === $this->value ? null : intval($this->value);
    }

    public function changeValue($value) : void
    {
        $this->value = intval($value);
    }

    public function parseValue(string $value) : bool
    {
        $matches = [];
        if (preg_match('/^'.$this->prefix.'([0-9]+)$/', $value, $matches)) {
            $this->value = intval($matches[1]);
            return true;
        }
        return false;
    }
}
