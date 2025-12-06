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
    use Traits\DateFilterTrait;
    use Traits\PhoneFilterTrait;
    use Traits\UrlFilterTrait;

    protected string $tag = 'li';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $style  = $component->getStyle();
        $value  = $component->getDataValue('value', true);
        $data   = $component->getData();
        $value  = $this->getMarkdownLine($value, ['a', 'strong', 'em', 'code'], $style, $inherited->getAppearance(), $inherited->hasATag(), $data['magicLinks'] ?? false);

        if (isset($data['formatDate'])) {
            $value = $this->parseDate($value, $data['formatDate']);
        }
        if (isset($data['formatPhone'])) {
            $value = $this->parsePhone($value, $data['formatPhone']);
        }
        if (isset($data['formatUrl'])) {
            $value = $this->parseUrl($value, $data['formatUrl']);
        }

        $component->addChild(new YamlComponentData(null, null, 'text', ['value' => $value, '_meta' => $component->getMeta()]));
        parent::build($component, $inherited);
    }
}
