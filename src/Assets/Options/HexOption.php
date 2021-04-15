<?php

declare(strict_types=1);

namespace Flipsite\Assets\Options;

final class HexOption extends AbstractImageOption
{
    public function getEncoded(?float $scale = null) : ?string
    {
        return null === $this->value ? null : $this->prefix.str_replace('#', '', $this->value);
    }

    public function getValue()
    {
        return null === $this->value ? null : $this->value;
    }

    public function changeValue($value) : void
    {
        $this->value = $value;
    }

    public function parseValue(string $value) : bool
    {
        $matches = [];
        if (preg_match('/^f([a-fA-F0-9]{6})$/', $value, $matches)) {
            $this->value = '#'.$matches[1];
        }
        return false;
    }
}
