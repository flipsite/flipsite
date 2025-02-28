<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use Flipsite\Data\YamlComponentData;

final class Icons extends AbstractGroup
{
    protected string $tag   = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $total = $data['total'] ?? 5;
        $count = $data['count'] ?? 4;
        $src   = $data['value'] ?? $data['src'] ?? 'zondicons/star.svg';
        $style = $component->getStyle();

        unset($data['total'], $data['count'], $data['src']);

        for ($i = 0; $i < $total; $i++) {
            $clonedInherited = clone $inherited;
            $clonedInherited->setParent($component->getId(), $component->getType());
            $dotComponentData  = new YamlComponentData($component->getPath(), null, 'icon', ['src' => $src], $i < $count ? $style['icon'] ?? [] : $style['officon'] ?? []);
            $icon              = $this->builder->build($dotComponentData, $clonedInherited);
            $this->addChild($icon);
        }

        parent::build($component, $inherited);
    }
}
