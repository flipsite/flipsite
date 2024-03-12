<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Utils\StyleAppearanceHelper;
use Flipsite\Components\Element;

trait ClassesTrait
{
    use BuilderTrait;
    private function addClassesToHtml(string $html, array $tags, array $style, string $appearance): string
    {
        foreach ($tags as $tag) {
            if (isset($style[$tag])) {
                $tag = $tag === 'tbl' ? 'table' : $tag;
                $tagStyle = StyleAppearanceHelper::apply($style[$tag], $appearance);
                if (!$tagStyle) {
                    continue;
                }
                $element = new Element('div');
                if (isset($tagStyle['background'])) {
                    $this->builder->handleBackground($element, $tagStyle['background']);
                    unset($tagStyle['background']);
                }
                $element->addStyle($tagStyle);
                $attributes = $element->renderAttributes();
                $html = str_replace('<' . $tag, '<' . $tag . ' '.$attributes, $html);
            }
        }
        return $html;
    }
}
