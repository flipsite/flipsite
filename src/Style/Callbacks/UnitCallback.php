<?php

declare(strict_types=1);

namespace Flipsite\Style\Callbacks;

class UnitCallback
{
    public const UNITS = ['%', 'px', 'em', 'vh', 'vw', 'vmin', 'vmax', 'ch','svh','lvh','dvh
    ','svw','lvw','dvw','svmin','lvmin','dvmin','svmax','lvmax','dvmax','vi','svi','lvi','dvi','vb','svb','lvb','dvb'];
    public function __invoke(array $args)
    {
        $multiplier = 1.0;
        if ('_multiplier' === end($args)) {
            array_pop($args);
            $multiplier = array_pop($args);
        }

        if (!isset($args[0]) || count($args) > 1) {
            return null;
        }

        if (is_numeric($args[0])) {
            $value = floatval($args[0]);
            $value *= $multiplier;
            return ($value / 4.0).'rem';
        }
        if (false !== mb_strpos($args[0], '/')) {
            $tmp     = explode('/', $args[0]);
            $value   = 100.0 * floatval($tmp[0]) / floatval($tmp[1]);
            $value *= $multiplier;
            return $value.'%';
        }
        if (str_starts_with($args[0], '[') && str_ends_with($args[0], ']')) {
            $value = substr($args[0], 1, strlen($args[0]) - 2);
            $unit = preg_replace('/[0-9]+/', '', $value);
            $value = floatval(str_replace($unit, '', $value));
            if (in_array($unit, self::UNITS)) {
                return $value.$unit;
            }
        }
        return null;
    }
}
