<?php

declare(strict_types=1);
namespace Flipsite\Style;

final class Style2
{
    private array $values = [
        'base' => [],
        'xs'   => [],
        'sm'   => [],
        'md'   => [],
        'lg'   => [],
        'xl'   => [],
        '2xl'  => [],
    ];
    public const CSS   = [
        'hover',
        'focus',
        'ltr',
        'rtl',
        'print'
    ];

    public function __construct(?string $encoded)
    {
        $classes = explode(' ', (string)$encoded);
        foreach ($classes as $cls) {
            $this->parse($cls);
        }
    }

    private function parse(string $class): void
    {
        $parts = explode(':', $class);
        $bp    = 'base';
        if (in_array($parts[0], array_keys($this->values), true)) {
            $bp = array_shift($parts);
        }
        $state = 'default';
        if (count($parts) > 1 && !in_array($parts[0], self::CSS, true)) {
            $state = array_shift($parts);
        }
        $this->values[$bp][$state] = implode(':', $parts);
    }

    public function hasState(string $state): bool
    {
        foreach ($this->values as $bp => $states) {
            if (isset($states[$state])) {
                return true;
            }
        }
        return false;
    }

    public function encodeState(string $state): string
    {
        $classes = [];
        foreach ($this->values as $bp => $states) {
            foreach ($states as $st => $class) {
                if ($st === $state) {
                    if ('base' === $bp) {
                        $classes[] = $class;
                    } else {
                        $classes[] = $bp.':'.$class;
                    }
                }
            }
        }
        return implode(' ', $classes);
    }
}
