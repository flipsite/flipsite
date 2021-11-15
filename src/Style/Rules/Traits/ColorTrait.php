<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules\Traits;

use SSNepenthe\ColorUtils\Colors\Color;
use SSNepenthe\ColorUtils\Colors\ColorFactory;
use SSNepenthe\ColorUtils\Transformers\Darken;
use SSNepenthe\ColorUtils\Transformers\Lighten;

trait ColorTrait
{
    protected function setColor(array $args, string $property, ?string $opacityVar = null) : bool
    {
        $color = $this->getColor($args);
        if (null === $color) {
            return false;
        }
        if (null === $opacityVar) {
            $this->setDeclaration($property, (string) $color);
        } else {
            $rgb  = $color->getRgb();
            $rgba = 'rgba('.$rgb->getRed().','.$rgb->getGreen();
            $rgba .= ','.$rgb->getBlue().',var('.$opacityVar.'))';
            $this->setDeclaration($opacityVar, $rgb->getAlpha());
            $this->setDeclaration($property, $rgba);
        }
        return true;
    }

    protected function getColor(array $args) : ?Color
    {
        if (0 === count($args)) {
            return null;
        }
        $colors = $this->getConfig('colors', $args[0]);
        if (null === $colors) {
            if (substr($args[0], 0, 2) === '[#') {
                $colors = substr($args[0], 1, 7);
            } else {
                return null;
            }
        }
        array_shift($args);
        if (is_string($colors)) {
            $colors = [500 => $colors];
        }

        if (isset($args[0]) && is_numeric($args[0])) {
            $shade = array_shift($args);
        } else {
            $shade = 500;
        }

        if (isset($colors[$shade])) {
            $color = ColorFactory::fromString($colors[$shade]);
        } else {
            $color = ColorFactory::fromString($colors[500]);
            $color = $this->getShade($color, intval($shade));
        }

        if (isset($args[0]) && is_string($args[0])) {
            $alpha = floatval(str_replace('o', '', $args[0]) / 100.0);
            $color = $color->with(['alpha' => $alpha]);
        }

        return $color;
    }

    private function getShade(Color $color, int $value = 500) : Color
    {
        $diff = 500 - $value;
        if (0 === $diff) {
            return $color;
        }
        $lightness = $color->getHsl()->getLightness();
        if ($diff > 0) {
            $max     = 100.0 - $lightness;
            $amount  = (500 - $value) / 500 * $max;
            $lighten = new Lighten($amount);
            return $lighten->transform($color);
        }
        $max    = $lightness;
        $amount = ($value - 500) / 500 * $max;
        $darken = new Darken($amount);
        return $darken->transform($color);
    }
}
