<?php

declare(strict_types=1);
namespace Flipsite\Style\Callbacks;

class ScreenWidthCallback
{
    public function __construct(private array $screens)
    {
    }

    public function __invoke(array $args)
    {
        if (isset($args[1]) && 'screen' === $args[0]) {
            $tmp   = explode('/', $args[1]);
            $value = $this->screens[$tmp[0]];
            if (isset($tmp[1])) {
                return intval(floatval($value) / floatval($tmp[1])).'px';
            }
            return intval($value).'px'; 
        }
        return null;
    }
}
