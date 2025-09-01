<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use Flipsite\Utils\RichtextHelper;

trait CheckTextTrait
{
    public function checkText(string $text, string $componentName): string
    {
        if (str_starts_with($text, '["')) {
            return implode(', ', json_decode($text, true));
        }
        if (RichtextHelper::isRichtext($text)) {
            $richtext = json_decode($text, true);
            foreach ($richtext as $item) {
                if ($item['type'] === 'p' && isset($item['value'])) {
                    return $item['value'];
                }
            }
        }
        return $text;
    }
}
