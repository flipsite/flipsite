<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use Flipsite\Data\YamlComponentData;

final class Icons extends AbstractGroup
{
    protected string $tag   = 'div';

    public function normalize(array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        return $data;
    }

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $total = $data['total'] ?? 5;
        $count = $data['count'] ?? 4;
        $src   = $data['src'] ?? 'zondicons/star.svg';
        $style = $component->getStyle();

        unset($data['total'], $data['count'], $data['src']);
        for ($i = 0; $i < $total; $i++) {
            $iconComponentData = new YamlComponentData(null, $component->getId(), 'icon', ['src' => $src], $i < $count ? $style['icon'] ?? [] : $style['officon'] ?? []);
            $component->addChild($iconComponentData);
        }

        parent::build($component, $inherited);
    }
}
