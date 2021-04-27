<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class UnitHelper
{
    public static function angle(string $value) : string
    {
        return $value.'deg';
    }

    public static function percentage(string $value) : string
    {
        return (string) (floatval($value) / 100.0);
    }
}
