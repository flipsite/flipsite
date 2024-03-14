<?php declare(strict_types=1);

namespace Flipsite\Utils;

use SSNepenthe\ColorUtils\Colors\Color;
use SSNepenthe\ColorUtils\Colors\ColorFactory;
use SSNepenthe\ColorUtils\Transformers\Darken;
use SSNepenthe\ColorUtils\Transformers\Lighten;
use SSNepenthe\ColorUtils\Transformers\Saturate;
use SSNepenthe\ColorUtils\Transformers\Desaturate;

class ColorScale {
    public function getLight(Color $color, int $index) : Color {
        $targetColor = ColorFactory::fromString('#ffffff');
        switch ($index) {
            case 1: return $this->decreaseAndLighten($color, $targetColor, 1.045);
            case 2: return $this->decreaseAndLighten($color, $targetColor, 1.08);
            case 3: return $this->decreaseAndLighten($color, $targetColor, 1.16);
            case 4: 
                $color = $this->decreaseAndLighten($color, $targetColor, 1.16);
                return $this->lightHover($color,7,5);
            case 5: 
                $color = $this->decreaseAndLighten($color, $targetColor, 1.16);
                return $this->lightHover($color,10,5);
            case 6:
                $color = $this->decreaseAndLighten($color, $targetColor, 1.16);
                return $this->lightHover($color,13,5);
            case 7: 
                $color = $this->decreaseAndLighten($color, $targetColor, 1.16);
                return $this->lightHover($color,16,6);
            case 8:
                $color = $this->decreaseAndLighten($color, $targetColor, 1.16);
                return $this->lightHover($color,19,6);
            case 9: return $color;
            case 10: return $this->lightHover($color, 6, 5);
            case 11: return $this->increaseAndDarken($color, $targetColor, 5);
            case 12: return $this->increaseAndDarken($color, $targetColor, 14);
        }
        return $targetColor;
    }
    public function getDark(Color $color, int $index) : Color {
        $targetColor = ColorFactory::fromString('#222');
        switch ($index) {
            case 1: return $this->decreaseAndDarken($color, $targetColor, 1.045);
            case 2: return $this->decreaseAndDarken($color, $targetColor, 1.08);
            case 3: return $this->decreaseAndDarken($color, $targetColor, 1.16);
            case 4: 
                $color = $this->decreaseAndDarken($color, $targetColor, 1.16);
                return $this->darkHover($color,7,5);
            case 5: 
                $color = $this->decreaseAndDarken($color, $targetColor, 1.16);
                return $this->darkHover($color,10,5);
            case 6:
                $color = $this->decreaseAndDarken($color, $targetColor, 1.16);
                return $this->darkHover($color,13,5);
            case 7: 
                $color = $this->decreaseAndDarken($color, $targetColor, 1.16);
                return $this->darkHover($color,16,6);
            case 8:
                $color = $this->decreaseAndDarken($color, $targetColor, 1.16);
                return $this->darkHover($color,19,6);
            case 9: return $color;
            case 10: return $this->darkHover($color, 3, 4);
            case 11: return $this->increaseAndLighten($color, $targetColor, 7);
            case 12: return $this->increaseAndLighten($color, $targetColor, 17);
        }
        return $targetColor;
    }
    private function lightHover(Color $color, int $darken, int $saturate) {
        if ($color->getHsl()->getSaturation() < 5) {
            $saturate = 0;
        }
        $color->getHsl()->getSaturation();
        $darken = new Darken($darken);
        $color = $darken->transform($color);
        if ($saturate) {
            $saturate = new Saturate($saturate);
            $color = $saturate->transform($color);
        }
        return $color;
    }
    private function darkHover(Color $color, int $lighten, int $saturate) {
        if ($color->getHsl()->getSaturation() < 5) {
            $saturate = 0;
        }
        $lighten = new Lighten($lighten);
        $color = $lighten->transform($color);
        if ($saturate) {
            $saturate = new Saturate($saturate);
            $color = $saturate->transform($color);
        }
        return $color;
    }
    private function increaseAndDarken(Color $color, Color $targetColor, float $targetRatio) : Color {
        $contrastRatio = $color->calculateContrastRatioWith($targetColor);
        $darken = new Darken(1);
        $i = 0;
        $lastContrestRatio = -1;
        if ($contrastRatio < $targetRatio) {
            while($contrastRatio < $targetRatio && $lastContrestRatio != $contrastRatio && $i<100) {
                $color = $darken->transform($color);
                $lastContrestRatio = $contrastRatio;
                $contrastRatio = $color->calculateContrastRatioWith($targetColor);
                $i++;
            }
        }
        return $color;
    }
    private function increaseAndLighten(Color $color, Color $targetColor, float $targetRatio) : Color {
        $contrastRatio = $color->calculateContrastRatioWith($targetColor);
        $darken = new Lighten(1);
        $i = 0;
        $lastContrestRatio = -1;
        if ($contrastRatio < $targetRatio) {
            while($contrastRatio < $targetRatio && $lastContrestRatio != $contrastRatio && $i<100) {
                $color = $darken->transform($color);
                $lastContrestRatio = $contrastRatio;
                $contrastRatio = $color->calculateContrastRatioWith($targetColor);
                $i++;
            }
        }
        return $color;
    }
    private function decreaseAndLighten(Color $color, Color $targetColor, float $targetRatio) : Color {
        $contrastRatio = $color->calculateContrastRatioWith($targetColor);
        $lighten = new Lighten(1);
        $i = 0;
        $lastContrestRatio = -1;
        if ($contrastRatio > $targetRatio) {
            while($contrastRatio > $targetRatio && $lastContrestRatio != $contrastRatio && $i<100) {
                $color = $lighten->transform($color);
                $lastContrestRatio = $contrastRatio;
                $contrastRatio = $color->calculateContrastRatioWith($targetColor);
                $i++;
            }
        }
        return $color;
    }
    private function decreaseAndDarken(Color $color, Color $targetColor, float $targetRatio) : Color {
        $contrastRatio = $color->calculateContrastRatioWith($targetColor);
        $darken = new Darken(1);
        $i = 0;
        $lastContrestRatio = -1;
        if ($contrastRatio > $targetRatio) {
            while($contrastRatio > $targetRatio && $lastContrestRatio != $contrastRatio && $i<100) {
                $color = $darken->transform($color);
                $lastContrestRatio = $contrastRatio;
                $contrastRatio = $color->calculateContrastRatioWith($targetColor);
                $i++;
            }
        }
        return $color;
    }
    
}