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

    public function getValue(?string $variant = null): ?string
    {
        if (!isset($this->styles[$variant])) {
            return null;
        }
        $values = array_filter($this->styles[$variant]->getValues());
        $baseValues = isset($this->styles['']) ? $this->styles['']->getValues() : [];
        $values = ArrayHelper::merge($baseValues, $values);

        $encoded = $values[''] ?? '';
        unset($values['']);
        foreach ($values as $key => $value) {
            $encoded .= ' '.$key.':'.$value;
        }
        return $encoded;
    }


}
