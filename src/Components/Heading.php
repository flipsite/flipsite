<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Heading extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\CheckTextTrait;

    protected bool $oneline = true;
    protected string $tag   = 'h2';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $style = $component->getStyle();
        $text = $data['value'] ?? '';
        $text = $this->checkText($text, 'Heading');
        if (!$text) {
            $this->render = false;
            return;
        }
        $html = $this->getMarkdownLine($text, ['a', 'strong'], $style, $inherited->getAppearance(), $inherited->hasATag());
        if (isset($data['anchor'])) {
            $a = new Element('a');
            $a->setContent($html);
            $a->setAttribute('href', '#'.$data['anchor']);
            $this->addChild($a);
        } else {
            $this->setContent($html);
        }
    }

    public function getDefaultStyle(): array
    {
        $htmlStyle            = $this->siteData->getHtmlStyle();
        $style                = $htmlStyle['heading'] ?? [];
        $style['font-family'] = 'font-headings';
        return $style;
    }
}
