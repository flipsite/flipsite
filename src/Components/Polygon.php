<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Polygon extends AbstractComponent
{
    protected string $tag  = 'svg';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        $this->setAttribute('viewBox', $data['viewBox'] ?? '0 0 100 100');
        $this->setAttribute('preserveAspectRatio', $data['preserveAspectRatio'] ?? 'none');
        $polygon = new Element('polygon');
        $polygon->setAttribute('points', $data['value'] ?? '50,0 100,0 50,100 0,100');
        $this->addChild($polygon);
    }

    public function getDefaultStyle(): array
    {
        return ['fill' => 'fill-current'];
    }
}
