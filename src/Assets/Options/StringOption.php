<?php

declare(strict_types=1);

namespace Flipsite\Assets\Options;

final class StringOption extends AbstractImageOption
{
    public function __construct(protected string $prefix, protected array $values)
    {
    }
    public function getEncoded(?float $scale = null): ?string
    {
        if (null === $this->value) {
            return null;
        }
        $flipped = array_flip($this->values);
        return $this->prefix.$flipped[$this->value];
    }

    public function getValue()
    {
        return null === $this->value ? null : $this->value;
    }

    public function changeValue($value): void
    {
        $this->value = $value;
    }

    public function parseValue(string $value): bool
    {
        $matches = [];
        if (preg_match('/^'.$this->prefix.'([a-z]+)$/', $value, $matches)) {
            $val = $matches[1];
            if (isset($this->values[$val])) {
                $this->value = $this->values[$val];
                return true;
            }
        }
        return false;
    }
}
