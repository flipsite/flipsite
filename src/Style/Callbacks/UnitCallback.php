<?php

declare(strict_types=1);
namespace Flipsite\Style\Callbacks;

class UnitCallback
{
    public function __invoke(array $args)
    {
        $multiplier = 1.0;
        if ('_multiplier' === end($args)) {
            array_pop($args);
            $multiplier = array_pop($args);
        }

        if (count($args) > 1) {
            return null;
        }

        if (is_numeric($args[0])) {
            $value = floatval($args[0]);
            $value*=$multiplier;
            return ($value / 4.0).'rem';
        }
        if (false !== mb_strpos($args[0], '/')) {
            $tmp     = explode('/', $args[0]);
            $value   = 100.0 * floatval($tmp[0]) / floatval($tmp[1]);
            $value*=$multiplier;
            return $value.'%';
        }
        if (str_starts_with($args[0], '[') && str_ends_with($args[0], ']')) {
            $value = substr($args[0], 1, strlen($args[0]) - 2);
            $units = ['%', 'px', 'em', 'vh', 'vw', 'vmin', 'vmax', 'ch'];
            foreach ($units as $unit) {
                if (false !== mb_strpos($value, $unit)) {
                    $value = floatval(str_replace($unit, '', $value));
                    $value*=$multiplier;
                    return $value.$unit;
                }
            }
        }
        return null;
    }
}
