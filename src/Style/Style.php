<?php

declare(strict_types=1);

namespace Flipsite\Style;

final class Style
{
    private array $values = ['' => ''];

    public function __construct(?string $encoded, private string $prefix = '')
    {
        $encoded         = ' :'.$encoded;
        $encodedVariants = explode(' ', str_replace($prefix, '', $encoded));
        foreach ($encodedVariants as $encodedVariant) {
            $parts                  = explode(':', $encodedVariant);
            $value                  = array_pop($parts);
            $variant                = trim(implode(':', $parts));
            $this->values[$variant] = $value;
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

    public function getValue(string $variant = ''): ?string
    {

        return $this->values[$variant] ?? null;
    }
    public function getOrderValue(string $variant): ?string
    {
        $values = [];
        $found = false;
        $base = null;
        foreach ($this->values as $key => $value) {
            if (!$key) {
                $base = $value;
            } elseif (strpos($key, $variant) !== false) {
                $found = true;
                $key = str_replace($variant, '', $key);
                $key = trim($key, ':');
                $values[] = $key ? $key.':'.$value : $key;
            }
        }
        if (!$found) {
            return null;
        }
        $needsBase = false;
        foreach ($values as $value) {
            if (false !== strpos($value, ':')) {
                $needsBase = true;
                break;
            }
        }
        if ($needsBase && $base) {
            array_unshift($values, $base);
        }
        return implode(' ', $values);
    }

    public function removeValue(string $variant): ?string
    {
        if ('' === $variant) {
            return null;
        }
        $value = $this->values[$variant] ?? null;
        unset($this->values[$variant]);
        return $value;
    }

    public function setValue(string $variant, string|int $value)
    {
        $this->values[$variant] = (string)$value;
    }

    public function encode(): string
    {
        $encoded = [];
        foreach ($this->values as $variant => $value) {
            if ('' === $variant) {
                $encoded[] = $this->prefix.(string)$value;
            } else {
                $encoded[] = $variant.':'.$this->prefix.(string)$value;
            }
        }
        $this->encoded = implode(' ', $encoded);
        return $this->encoded;
    }
}
