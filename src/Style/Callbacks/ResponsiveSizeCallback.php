<?php

declare(strict_types=1);

namespace Flipsite\Style\Callbacks;

use Flipsite\Utils\CanIUse;

class ResponsiveSizeCallback
{
    private bool $isSupported = false;

    public function __construct(private array $screens)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        //$userAgent = 'Mozilla/5.0 (Linux; Android 9; SAMSUNG SM-G975F Build/PPR1.180610.011) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/9.2 Chrome/67.0.3396.87 Mobile Safari/537.36';
        //$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8';
        // $userAgent = 'Mozilla/5.0 (Windows NT 6.2; Trident/7.0; rv:11.0) like Gecko';
        // $userAgent = 'Mozilla/5.0 (iPad; CPU OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1';
        // $userAgent = 'Opera/9.80 (Android; Opera Mini/12.0.1987/37.7327; U; pl) Presto/2.12.423 Version/12.16';

        $this->isSupported = CanIUse::cssMathFunctions($userAgent);
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
        $minSizeRem = $minSizePx / 16.0;
        $maxSizeRem = $maxSizePx / 16.0;

        if (!$this->isSupported) {
            return ($minSizeRem + 0.5*($maxSizeRem-$minSizeRem)).'rem';
        }

        $slope       = ($maxSizePx - $minSizePx) / ($maxScreenPx / 100 - $minScreenPx / 100);
        $interceptPx = round($maxSizePx - $slope * $maxScreenPx / 100, 2);

        if (0.0 === $interceptPx) {
            $between = $slope.'vw';
        } else {
            $between = round($interceptPx / 16.0, 2).'rem + '.$slope.'vw';
        }

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
