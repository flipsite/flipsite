<?php

declare(strict_types=1);

namespace Flipsite\Style\Callbacks;

class UnitCallback
{
    public function __invoke(array $args)
    {
        if (is_numeric($args[0])) {
            $spacing = floatval($args[0]);
            return ($spacing / 4.0).'rem';
        }
        if (false !== mb_strpos($args[0], '/')) {
            $tmp     = explode('/', $args[0]);
            $spacing = 100.0 * floatval($tmp[0]) / floatval($tmp[1]);
            return $spacing.'%';
        }
        $units = ['px', 'em', 'vh', 'vw', 'vmin', 'vmax', 'ch'];
        foreach ($units as $unit) {
            if (false !== mb_strpos($args[0], $unit)) {
                $spacing = floatval(str_replace($unit, '', $args[0]));
                return $spacing.$unit;
            }
        }
        return null;
    }
}
