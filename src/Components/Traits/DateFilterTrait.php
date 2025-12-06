<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Utils\FormatHelper;
use Flipsite\Utils\Language;

trait DateFilterTrait
{
    use PathTrait;
    protected function parseDate(string $value, Language $language, string $format = 'none'): string
    {
        $pattern = '/\b\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])\b/';
        preg_match_all($pattern, $value, $matches);
        if ($matches[0]) {
            $matches = array_unique($matches[0]);
            foreach ($matches as $match) {
                $value = str_replace($match, FormatHelper::convertToDateFormat($match, $language, $format), $value);
            }
        }

        return $value;
    }
}
