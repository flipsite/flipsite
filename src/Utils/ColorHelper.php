<?php

declare(strict_types=1);

namespace Flipsite\Utils;

use SSNepenthe\ColorUtils\Colors\Color;
use SSNepenthe\ColorUtils\Colors\ColorFactory;
use SSNepenthe\ColorUtils\Transformers\Darken;
use SSNepenthe\ColorUtils\Transformers\Lighten;
use SSNepenthe\ColorUtils\Transformers\Desaturate;

class ColorHelper
{
    public static function getGray(string $colorString, int $desaturate = 90, int $minBrightness = 120) : string {
        $color = ColorFactory::fromString($colorString);
        $transform = new Desaturate($desaturate);
        $color = $transform->transform($color);
        $transform = new Darken(1);
        $i = 0;
        while( $color->calculatePerceivedBrightness() > $minBrightness && $i < 100) {
            $color = $transform->transform($color);
        }
        return sprintf("#%02x%02x%02x", $color->getRed(), $color->getGreen(), $color->getBlue());
    }
    public static function parseAndReplace(string $colorString, array $allColors): string
    {
        $pattern = '/('.implode('|', array_keys($allColors)).')(-[0-9]{1,3})?(\/[0-9]{1,3})?/';
        $matches = [];
        preg_match_all($pattern, $colorString, $matches);
        foreach ($matches[0] as $match) {
            $color = ColorHelper::getColor($match, $allColors);
            $pos = strpos($colorString, $match);
            if ($pos !== false) {
                $colorString = substr_replace($colorString, (string)$color, $pos, strlen($match));
            }
        }
        return $colorString;
    }

    public static function getColorString(string|array $args, array $allColors): ?string {
        $color = self::getColor($args, $allColors);
        if (null === $color) {
            return null;
        }
        if ($color->getAlpha() < 1.0) {
            return (string)$color;
        }
        return sprintf("#%02x%02x%02x", $color->getRed(), $color->getGreen(), $color->getBlue());
    }

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

        $shade = 500;
        if (isset($args[0])) {
            if (is_numeric($args[0])) {
                $shade = intval($args[0]);
            } else {
                $tmp = explode('/', array_shift($args));
                if (isset($tmp[1])) {
                    $alpha = floatval(array_pop($tmp)) / 100.0;
                }
                if (is_numeric($tmp[0])) {
                    $shade = $tmp[0];
                } else if (is_string($tmp[0])) {
                    $color = ColorFactory::fromString($colors[500]);
                    if ($tmp[0] === 'contrast') {
                        $brightness = $color->getRgb()->calculatePerceivedBrightness();
                        if ($brightness < 128) {
                            return ColorFactory::fromString('#ffffff');
                        } else {
                            $colorScale = new ColorScale();
                            $contrastColor = $colorScale->getLight($color, 12);
                            $contrastRatio = $contrastColor->calculateContrastRatioWith($color);
                            if ($contrastRatio < 4.5) {
                                return ColorFactory::fromString('#000000');
                            } else {
                                return $contrastColor;
                            }
                        }
                    }
                    $shades = [
                        'l1','l2','l3','l4','l5','l6','l7','l8','l10','l11','l12',
                        'd1','d2','d3','d4','d5','d6','d7','d8','d10','d11','d12'
                    ];
                    if (in_array($tmp[0], $shades)) {
                        $colorScale = new ColorScale();
                        $isLight = substr($tmp[0], 0, 1) === 'l';
                        $index = intval(substr($tmp[0], 1));
                        if ($isLight) {
                            return $colorScale->getLight($color, $index);
                        } else {
                            return $colorScale->getDark($color, $index);
                        }
                    }
                }
            }
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
