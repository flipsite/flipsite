<?php

declare(strict_types=1);

namespace Flipsite\Style\Callbacks;

class BgGradientCallback
{
    public function __invoke(array $args) : ?string
    {
        if ('gradient' !== $args[0]) {
            return null;
        }
        if ('to' === $args[1]) {
            switch ($args[2]) {
                case 'b':
                    $gradientDirection = 'to bottom';
                    break;
                case 'bl':
                    $gradientDirection = 'to bottom left';
                    break;
                case 'br':
                    $gradientDirection = 'to bottom right';
                    break;
                case 'l':
                    $gradientDirection = 'to left';
                    break;
                case 'r':
                    $gradientDirection = 'to right';
                    break;
                case 't':
                    $gradientDirection = 'to top';
                    break;
                case 'tl':
                    $gradientDirection = 'to left';
                    break;
                case 'tr':
                    $gradientDirection = 'to right';
                    break;
                default:
                    $gradientDirection = '0deg';
            }
        } else {
            $gradientDirection = intval($args[2]).'deg';
        }
        return 'linear-gradient('.$gradientDirection.', var(--tw-gradient-stops))';
    }
}
