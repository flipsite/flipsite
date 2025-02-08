<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\YamlComponentData;
use Flipsite\Data\InheritedComponentData;

final class Li extends AbstractGroup
{
    use Traits\MarkdownTrait;
    use Traits\ClassesTrait;
    use Traits\SiteDataTrait;
    use Traits\BuilderTrait;

    protected string $tag = 'li';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $style = $component->getStyle();
        $text = $component->getDataValue('value', true);
        $html = $this->getMarkdownLine($text ?? '');
        $html = $this->addClassesToHtml($html, ['a', 'strong','em','code'], $style, $inherited->getAppearance());

        $component->addChild(new YamlComponentData(null, null, 'text', ['value' => $html]));
        parent::build($component, $inherited);
    }
}
