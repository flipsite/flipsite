<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

trait CheckTextTrait
{
    public function checkText(string $text, string $componentName): string
    {
        if (str_starts_with($text, '["')) {
            return implode(', ', json_decode($text, true));
        }
        if (str_starts_with($text, '[{"type"')) {
            return 'Richtext value not supported in '.$componentName.'.';
        }
        return $text;
    }
}
