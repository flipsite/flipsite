<?php

declare(strict_types=1);

namespace Flipsite\Utils;

use SSNepenthe\ColorUtils\Colors\Color;
use SSNepenthe\ColorUtils\Colors\ColorFactory;
use SSNepenthe\ColorUtils\Transformers\Darken;
use SSNepenthe\ColorUtils\Transformers\Lighten;

class ColorHelper
{
    public static function parseAndReplace(string $colorString, array $allColors) : string {
        $pattern = '/('.implode('|',array_keys($allColors)).')-[1-9]{1}00(\/[0-9]+)?/';
        $matches = [];
        preg_match_all($pattern, $colorString, $matches);
        foreach ($matches[0] as $match) {
            $color = ColorHelper::getColor($match, $allColors);
            $colorString = str_replace($match,(string)$color, $colorString);
        }

        $pattern = '/(white|black)(\/[0-9]+)?/';
        $matches = [];
        preg_match_all($pattern, $colorString, $matches);
        foreach ($matches[0] as $match) {
            $color = ColorHelper::getColor($match, $allColors);
            $colorString = str_replace($match,(string)$color, $colorString);
        }

        return $colorString;
    }




    // $matches = [];
    // preg_match_all('/(primary|secondary|light|dark)-[1-9]{1}00(\/[0-9]+)?/', $gradient, $matches);
    
    // foreach ($matches[0] as $color) {
    //     $tmp = explode('/', $color);
    //     $alpha = floatVal($tmp[1] ?? 100.0) / 100.0;
    //     if ($alpha > 1.0) $alpha = 1.0;
    //     $colorShade = explode('-', $tmp[0]);
    //     $shade = $colorShade[1] ?? 500;
    //     $hex = $colors[$colorShade[0]];


    //     echo $hex.'('.$shade.') '.$alpha.'<br>' ;
    // }
    
    // return $gradient;


    public static function getColor(string|array $args, array $allColors): ?Color
    {
        if (is_string($args)) {
            $args = explode('-', $args);
        }
        if (0 === count($args)) {
            return null;
        }
        $tmp    = explode('/', $args[0]);
        $colors = $allColors[$tmp[0]] ?? null;
        if (null === $colors) {
            if (substr($args[0], 0, 2) === '[#') {
                $colors = substr($args[0], 1, 7);
            } else {
                return null;
            }
        }
        $alpha = null;
        if (isset($tmp[1]) && is_numeric($tmp[1])) {
            $alpha = floatval($tmp[1]) / 100.0;
        }
        array_shift($args);

        if (is_string($colors)) {
            $colors = [500 => $colors];
        }

        if (isset($args[0]) && is_numeric($args[0])) {
            $shade = array_shift($args);
        } elseif (isset($args[0]) && is_string($args[0]) && strpos($args[0], '/')) {
            $tmp = explode('/', array_shift($args));
            if (count($tmp) === 2) {
                $shade = intval($tmp[0]);
                $alpha = floatval($tmp[1]) / 100.0;
            }
        } else {
            $shade = 500;
        }

        if (isset($colors[$shade])) {
            try {
                $color = ColorFactory::fromString($colors[$shade]);
            } catch (\Exception $e) {
                // Check if reference to other color
                $args = explode('-', $colors[$shade]);
                return self::getColor($args, $allColors);
            }
        } else {
            $color = ColorFactory::fromString($colors[500]);
            $color = self::getShade($color, intval($shade));
        }

        // Old deprecated oValue opacity
        if (isset($args[0]) && is_string($args[0])) {
            $alpha = floatval(str_replace('o', '', $args[0]) / 100.0);
        }

        if (null !== $alpha) {
            $color = $color->with(['alpha' => $alpha]);
        }

        return $color;
    }

    public static function getShade(Color $color, int $value = 500): Color
    {
        $diff = 500 - $value;
        if (0 === $diff) {
            return $color;
        }
        $lightness = $color->getHsl()->getLightness();
        if ($diff > 0) {
            $max     = 100.0 - $lightness;
            $amount  = (500 - $value) / 500 * $max;
            if (!$amount) {
                return $color;
            }
            $lighten = new Lighten($amount);
            return $lighten->transform($color);
        }
        $max    = $lightness;
        $amount = ($value - 500) / 500 * $max;
        if (!$amount) {
            return $color;
        }
        $darken = new Darken($amount);
        return $darken->transform($color);
    }
}
