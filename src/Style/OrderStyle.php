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

        $orders = ['first', 'last', 'odd', 'even'];
        foreach ($encodedVariants as $variant) {
            $found = false;
            foreach ($orders as $order) {
                if (strpos($variant, $order) !== false) {
                    $variantWithoutOrder = str_replace($order.':', '', $variant);
                    $this->styles[$order] = new Style($variantWithoutOrder);
                    $found = true;
                }
            }
            if (!$found) {
                $this->styles[''] = new Style($variant);
            }
        }

    }

    public function getValue(?string $variant = null): ?string
    {
        if (!isset($this->styles[$variant])) {
            return null;
        }
        $values = $this->styles[$variant]->getValues();
        $baseValues = isset($this->styles['']) ? $this->styles['']->getValues() : [];

        $values = ArrayHelper::merge($baseValues, $values);
        $encoded = '';
        foreach ($values as $key => $value) {
            if (!$key) {
                $encoded .= $value;
            } else {
                $encoded .= ' '.$key.':'.$value;
            }
        }
        return $encoded;
    }


}
