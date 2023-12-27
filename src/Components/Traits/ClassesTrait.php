<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Utils\StyleAppearanceHelper;

trait ClassesTrait
{
    private function addClassesToHtml(string $html, array $tags, array $style, string $appearance): string
    {
        foreach ($tags as $tag) {
            if (isset($style[$tag])) {
                $tag = $tag === 'tbl' ? 'table' : $tag;
                $tagStyle = StyleAppearanceHelper::apply($style[$tag], $appearance);
                $html = str_replace('<' . $tag, '<' . $tag . ' class="' . implode(' ', $tagStyle) . '"', $html);
            }
        }
        return $html;
    }
}
