<?php

declare(strict_types=1);

namespace Flipsite\Style\Callbacks;

class ResponsiveSizeCallback
{
    private array $screens;

    public function __construct(array $screens)
    {
        $this->screens = $screens;
    }

    public function __invoke(array $args)
    {
        $tmp = explode('|', $args[0]);
        if (2 === count($tmp)) {
            $args = ['xs', $tmp[0], 'xl', $tmp[1]];
        }
        if (4 !== count($args)) {
            return null;
        }

        $minScreenPx = floatval($this->screens[$args[0]]);
        $minSizePx   = floatval($this->getPx($args[1]));
        $maxScreenPx = floatval($this->screens[$args[2]]);
        $maxSizePx   = floatval($this->getPx($args[3]));

        $slope       = ($maxSizePx - $minSizePx) / ($maxScreenPx / 100 - $minScreenPx / 100);
        $interceptPx = round($maxSizePx - $slope * $maxScreenPx / 100, 2);

        if (0.0 === $interceptPx) {
            $between = $slope.'vw';
        } else {
            $between = round($interceptPx / 16.0, 2).'rem + '.$slope.'vw';
        }
        $minSizeRem = $minSizePx / 16.0;
        $maxSizeRem = $maxSizePx / 16.0;
        return 'min(max('.$minSizeRem.'rem,'.$between.'),'.$maxSizeRem.'rem)';
    }

    private function getPx($value) : float
    {
        if (is_numeric($value)) {
            $value = floatval($value) * 4.0;
        } elseif (mb_strpos($value, 'px')) {
            $value = floatval(str_replace('px', '', $value));
        }
        return $value ?? 0.0;
    }
}
