<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait AspectRatioTrait
{
    protected function gcd(int $a, int $b) : int
    {
        return $b == 0 ? $a : $this->gcd($b, $a % $b);
    }

    protected function simplifyAspectRatio(int $width, int $height) : array
    {
        $divisor = $this->gcd($width, $height);
        return [$width / $divisor, $height / $divisor];
    }
}
