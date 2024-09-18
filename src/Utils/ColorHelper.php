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
    public static function getGray(?string $colorString, int $desaturate = 90, int $minBrightness = 60): string
    {
        $color     = ColorFactory::fromString($colorString ?? '#121212');
        $transform = new Desaturate($desaturate);
        $color     = $transform->transform($color);
        $transform = new Darken(1);
        $i         = 0;
        while ($color->calculatePerceivedBrightness() > $minBrightness && $i < 100) {
            $color = $transform->transform($color);
        }
        return sprintf('#%02x%02x%02x', $color->getRed(), $color->getGreen(), $color->getBlue());
    }

    public static function parseAndReplace(string $colorString, array $allColors): string
    {
        $pattern = '/('.implode('|', array_keys($allColors)).')(-[dl0-9]{1,3})?(\/[dl0-9]{1,3})?/';
        $matches = [];
        preg_match_all($pattern, $colorString, $matches);
        foreach ($matches[0] as $match) {
            $color = ColorHelper::getColor($match, $allColors);
            $pos   = strpos($colorString, $match);
            if ($pos !== false) {
                $colorString = substr_replace($colorString, (string)$color, $pos, strlen($match));
            }
        }
        return $colorString;
    }

    public static function getColorString(string|array $args, array $allColors): ?string
    {
        $color = self::getColor($args, $allColors);
        if (null === $color) {
            return null;
        }
        if ($color->getAlpha() < 1.0) {
            return (string)$color;
        }
        return sprintf('#%02x%02x%02x', $color->getRed(), $color->getGreen(), $color->getBlue());
    }

    public static function getColor(string|array $args, array $allColors): ?Color
    {
        if (is_array($args)) {
            $args = implode('-', $args);
        }
        if (!$args) {
            return null;
        }

        $contrast = false;
        if (str_ends_with($args, '-contrast')) {
            $contrast = true;
            $args     = substr($args, 0, strlen($args) - 9);
        }

        $alpha = 1.0;
        $tmp   = explode('/', $args);
        if (count($tmp) === 2) {
            $alpha = floatval($tmp[1]) / 100.0;
            $args  = $tmp[0];
        }

        $color = null;

        if (str_starts_with($args, '{') && str_ends_with($args, '}')) {
            return null;
        }

        if (substr($args, 0, 2) === '[#' && strlen($args) === 9) {
            $color = ColorFactory::fromString(substr($args, 1, 7));
        } else {
            $shade      = 500;
            $tmp        = explode('-', $args);
            $themeColor = $tmp[0];
            if (!isset($allColors[$themeColor])) {
                return null;
            }
            if (is_string($allColors[$themeColor])) {
                $allColors[$themeColor] = ['500' => $allColors[$themeColor]];
            }
            if (isset($tmp[1])) {
                $shades = [
                    'l1', 'l2', 'l3', 'l4', 'l5', 'l6', 'l7', 'l8', 'l10', 'l11', 'l12',
                    'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7', 'd8', 'd10', 'd11', 'd12'
                ];
                if (in_array($tmp[1], $shades)) {
                    $color      = ColorFactory::fromString($allColors[$themeColor][500]);
                    $colorScale = new ColorScale();
                    $isLight    = substr($tmp[1], 0, 1) === 'l';
                    $index      = intval(substr($tmp[1], 1));
                    if ($isLight) {
                        $color = $colorScale->getLight($color, $index);
                    } else {
                        $color = $colorScale->getDark($color, $index);
                    }
                } elseif (is_numeric($tmp[1])) {
                    $shade = intval($tmp[1]);
                }
            }
            if (!$color && isset($allColors[$themeColor])) {
                $color = ColorFactory::fromString($allColors[$themeColor][500]);
                $color = self::getShade($color, intval($shade));
            }
        }

        if ($contrast) {
            $brightness = $color->getRgb()->calculatePerceivedBrightness();
            if ($brightness < 128) {
                $color =  ColorFactory::fromString('#ffffff');
            } else {
                $colorScale    = new ColorScale();
                $color         = $colorScale->getLight($color, 12);
                $contrastRatio = $color->calculateContrastRatioWith($color);
                if ($contrastRatio < 4.5) {
                    $color = ColorFactory::fromString('#000000');
                }
            }
        }

        if ($alpha != 1.0) {
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
