<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class RichtextHelper
{
    public static function fallbackFromString(string $string) : array
    {
        $items = explode('</p>', $string);
        $json  = [];
        foreach ($items as $item) {
            $val = trim(strip_tags($item));
            if ($val) {
                $json[] = [
                    'type'   => 'p',
                    'value'  => $val
                ];
            }
        }
        return $json;
    }
}
