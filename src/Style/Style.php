<?php

declare(strict_types=1);

namespace Flipsite\Style;

final class Style
{
    private $isDark = false;
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
        if (strpos((string)$encoded, 'dark:') !== false) {
            $this->isDark = true;
            $encoded = str_replace('dark:', '', (string)$encoded);
        }
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
        $this->values[$bp][$state] ??= [];
        $this->values[$bp][$state][] = implode(':', $parts);
    }

    public function handleOrder(int $total, int $index)
    {
        if ($this->hasState('first') === false &&
            $this->hasState('last') === false &&
            $this->hasState('even') === false &&
            $this->hasState('odd') === false) {
            return;
        }
        $order = 'odd';
        if ($index === 0) {
            $order = 'first';
        } elseif ($index === $total - 1) {
            $order = 'last';
        } elseif ($total > 2 && $index % 2 === 0) {
            $order = 'even';
        }
        foreach ($this->values as $bp => &$states) {
            if (isset($states[$order])) {
                $states['default'] = $states[$order];
            }
            unset($states['odd'], $states['even'], $states['first'], $states['last']);
        }
    }

    public function handleNavState(string $navState)
    {
        if ($this->hasState('nav-exact') === false &&
            $this->hasState('nav-active') === false) {
            return;
        }
        foreach ($this->values as $bp => &$states) {
            if ('exact' === $navState && isset($states['nav-exact'])) {
                $states['default'] = $states['nav-exact'];
            } elseif ($navState && isset($states['nav-active'])) {
                $states['default'] = $states['nav-active'];
            }
            unset($states['nav-active'], $states['nav-exact']);
        }
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
            foreach ($states[$state] ?? [] as $class) {
                if ($bp === 'base') {
                    $classes[] = $class;
                } else {
                    $classes[] = $bp . ':' . $class;
                }
            }
        }
        if ($this->isDark) {
            array_walk($classes, function (&$item) {
                $item = 'dark:' . $item;
            });
        }
        return implode(' ', $classes);
    }

    public function removeState(string $state): string
    {
        $encodedState = $this->encodeState($state);
        foreach ($this->values as $bp => &$states) {
            if (isset($states[$state])) {
                unset($states[$state]);
            }
        }
        return $encodedState;
    }
    public function encode(): string
    {
        $classes = [];
        foreach ($this->values as $bp => $states) {
            foreach ($states as $st => $stateClasses) {
                if ($st !== 'default') {
                    continue;
                }
                foreach ($stateClasses as $class) {
                    if ($bp === 'base') {
                        $classes[] = $class;
                    } else {
                        $classes[] = $bp . ':' . $class;
                    }
                }
            }
        }
        if ($this->isDark) {
            array_walk($classes, function (&$item) {
                $item = 'dark:' . $item;
            });
        }
        return implode(' ', $classes);
    }
}
