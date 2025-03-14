<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\YamlComponentData;
use Flipsite\Data\InheritedComponentData;

final class Li extends AbstractGroup
{
    use Traits\MarkdownTrait;
    use Traits\SiteDataTrait;
    use Traits\BuilderTrait;

    protected string $tag = 'li';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $style = $component->getStyle();
        $value  = $component->getDataValue('value', true);
        $value = $this->getMarkdownLine($value, ['a', 'strong', 'em', 'code'], $style, $inherited->getAppearance());
        $component->addChild(new YamlComponentData(null, null, 'text', ['value' => $value, '_meta' => $component->getMeta()]));
        parent::build($component, $inherited);
    }
}
