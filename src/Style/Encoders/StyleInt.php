<?php

declare(strict_types=1);
namespace Flipsite\Style\Encoders;

final class StyleInt
{
    private array $values = [''=>0];

    public function __construct(private string $encoded, private string $prefix)
    {
        $tmp = explode(' ', str_replace($prefix, '', $encoded));
        foreach ($tmp as $t) {
            $tmp2                         = explode(':', $t);
            $value                        = intval(array_pop($tmp2));
            $this->values[$tmp2[0] ?? ''] = $value;
        }
    }

    public function getVariants() : array
    {
        return array_keys($this->values);
    }

    public function getValue(string $variant = '') : int
    {
        return $this->values[$variant] ?? 0;
    }

    public function addValue(int $add, string $variant = '')
    {
        if (!isset($this->values[$variant])) {
            return;
        }
        $this->values[$variant] += $add;
    }

    public function encode() : string
    {
        $encoded = [];
        foreach ($this->values as $variant => $value) {
            $encoded[] = ($variant ? $variant.':'.$this->prefix.(string)$value : $this->prefix.(string)$value);
        }
        $this->encoded = implode(' ', $encoded);
        return $this->encoded;
    }
}
