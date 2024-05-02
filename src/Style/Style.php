<?php

declare(strict_types=1);

namespace Flipsite\Style;

final class Style
{
    private array $values = ['base' => 0];

    public function __construct(?string $encoded, private string $prefix = '')
    {
        $encoded = 'base:'.$encoded;
        $encodedVariants = explode(' ', str_replace($prefix, '', $encoded));
        foreach ($encodedVariants as $encodedVariant) {
            $parts                         = explode(':', $encodedVariant);
            $value                        = array_pop($parts);
            $key = implode(':', $parts);
            $this->values[$key] = $value;
        }
    }

    public function getVariants(): array
    {
        return array_keys($this->values);
    }

    public function hasVariant(string $variant): bool
    {
        return isset($this->values[$variant]);
    }

    public function getValue(string $variant = 'base'): ?string
    {
        return $this->values[$variant] ?? null;
    }

    public function removeValue(string $variant): ?string
    {
        if ('base' === $variant) {
            return null;
        }
        $value = $this->values[$variant] ?? null;
        unset($this->values[$variant]);
        return $value;
    }

    public function setValue(string|int $value, string $variant = 'base')
    {
        $this->values[$variant] = (string)$value;
    }
    public function encode(): string
    {
        $encoded = [];
        foreach ($this->values as $variant => $value) {
            if ('base' === $variant) {
                $encoded[] = $this->prefix.(string)$value;
            } else {
                $encoded[] = $variant.':'.$this->prefix.(string)$value;
            }
        }
        $this->encoded = implode(' ', $encoded);
        return $this->encoded;
    }
}
