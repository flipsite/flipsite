<?php

declare(strict_types=1);
namespace Flipsite\Style\Callbacks;

class ResponsiveSizeCallback
{
    public function __construct(private array $screens, private bool $isCssMathFunctionsSupported)
    {
    }

    public function __invoke(array $args)
    {
        $screens = array_keys($this->screens);
        $tmp     = explode('|', $args[0]);
        if (2 === count($tmp) && count($args) === 1) {
            $args = ['xs', $tmp[0], 'xl', $tmp[1]];
        }

        if (count($args) !== 4 || !in_array($args[0], $screens) || !in_array($args[2], $screens)) {
            return null;
        }

        $minScreenPx = floatval($this->screens[$args[0]]);
        $minSizePx   = floatval($this->getPx($args[1]));
        $maxScreenPx = floatval($this->screens[$args[2]]);
        $maxSizePx   = floatval($this->getPx($args[3]));
        $minSizeRem  = $minSizePx / 16.0;
        $maxSizeRem  = $maxSizePx / 16.0;

        if (!$this->isCssMathFunctionsSupported) {
            return ($minSizeRem + 0.5 * ($maxSizeRem - $minSizeRem)) . 'rem';
        }

        $slope       = ($maxSizePx - $minSizePx) / ($maxScreenPx / 100 - $minScreenPx / 100);
        $interceptPx = round($maxSizePx - $slope * $maxScreenPx / 100, 2);

        if (0.0 === $interceptPx) {
            $between = $slope . 'vw';
        } else {
            $between = round($interceptPx / 16.0, 2) . 'rem + ' . round($slope, 2) . 'vw';
        }

        return 'min(max(' . $minSizeRem . 'rem,' . $between . '),' . $maxSizeRem . 'rem)';
    }

    private function getPx($value): float
    {
        if (is_numeric($value)) {
            $value = floatval($value) * 4.0;
        } elseif (mb_strpos($value, 'px')) {
            $value = str_replace('[', '', $value);
            $value = str_replace(']', '', $value);
            $value = floatval(str_replace('px', '', $value));
        }
        return $value ?? 0.0;
    }
}
