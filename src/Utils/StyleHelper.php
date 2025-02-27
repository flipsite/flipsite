<?php

declare(strict_types=1);
namespace Flipsite\Utils;

class StyleHelper
{
    public static function decodeString(string $style, array $variants = []) : array
    {
        $classes = [];
        $parts   = explode(' ', $style);
        foreach ($parts as $cls) {
            $base = true;
            foreach ($variants as $v) {
                if (strpos($cls, $v.':') === 0) {
                    $cls         = str_replace($v.':', '', $cls);
                    $classes[$v] = $cls;
                    $base        = false;
                }
            }
            if ($base) {
                $classes['base'] = $cls;
            }
        }
        return $classes;
    }
}
