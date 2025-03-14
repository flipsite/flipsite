<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\SiteDataTrait;

    protected string $tag = 'p';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $style = $component->getStyle();
        $html = $this->getMarkdownLine($data['value'] ?? '', ['a', 'strong', 'em', 'code'], $style, $inherited->getAppearance());
        $this->setContent((string)$html);
    }
}
