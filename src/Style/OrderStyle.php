<?php

declare(strict_types=1);

namespace Flipsite\Style;

use Flipsite\Utils\ArrayHelper;

final class OrderStyle
{
    private array $styles = [];

    public function __construct(?string $encoded, private string $prefix = '')
    {
        $encodedVariants = explode(' ', str_replace($prefix, '', $encoded));

        $variants = [];
        $orders = ['first', 'last', 'odd', 'even'];
        foreach ($encodedVariants as $variant) {
            $found = false;
            foreach ($orders as $order) {
                if (strpos($variant, $order) !== false) {
                    $variants[$order] ??= '';
                    $variants[$order] .= ' '.str_replace($order.':', '', $variant);
                    $found = true;
                }
            }
            if (!$found) {
                $variants[''] ??= '';
                $variants[''] .= ' '.$variant;
            }
        }
        foreach ($variants as $order => $variant) {
            $this->styles[$order] = new Style($variant);
        }

    }

    public function getValue(int $row, int $total): ?string
    {
        $isEven = $row % 2 === 0;
        $isOdd = !$isEven;
        $isFirst = $row === 1;
        $isLastEven = $row === $total && $total % 2 === 0;
        $isLastOdd = $row === $total && $total % 2 === 1;

        $baseValues = isset($this->styles['']) ? $this->styles['']->getValues() : [];
        if ($isFirst) {
            $values = $this->styles['first'] ?? $this->styles['odd'] ?? null;
        } elseif ($isLastEven) {
            $values = $this->styles['last'] ?? $this->styles['even'] ?? null;
        } elseif ($isLastOdd) {
            $values = $this->styles['last'] ?? $this->styles['odd'] ?? null;
        } elseif ($isOdd) {
            $values = $this->styles['odd'] ?? null;
        } elseif ($isEven) {
            $values = $this->styles['even'] ?? null;
        }

        $baseValues = isset($this->styles['']) ? $this->styles['']->getValues() : [];
        $values = $values ? ArrayHelper::merge($baseValues, array_filter($values->getValues())) : $baseValues;

        $encoded = $values[''] ?? '';
        unset($values['']);
        foreach ($values as $key => $value) {
            $encoded .= ' '.$key.':'.$value;
        }
        return $encoded;
    }


}
