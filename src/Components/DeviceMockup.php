<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\YamlComponentData;
use Flipsite\Data\InheritedComponentData;

final class DeviceMockup extends AbstractGroup
{
    protected string $tag  = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data               = $component->getData();
        $screenMap          = explode(',', $data['screenMap'] ?? '0,0,1,0,1,1,0,1');
        $container          = new YamlComponentData($component->getPath(), $component->getId().'.container', 'group', [], ['position' => 'relative']);
        $clonedInherited    = clone $inherited;
        $clonedInherited->setIsComponent(false);
        $containerComponent = $this->builder->build($container, $clonedInherited);

        $deviceData  = new YamlComponentData($component->getPath(), $component->getId().'.device', 'image', [
            'value' => $data['device'] ?? '',
            'alt'   => '',
        ], ['maxWidth' => 'max-w-none', 'width' =>  'w-full']);
        $containerComponent->addChild($this->builder->build($deviceData, $clonedInherited));

        $screenshotData  = new YamlComponentData($component->getPath(), $component->getId().'.device', 'image', [
            'value'  => $data['screenshot'] ?? '',
            'alt'    => $data['alt'] ?? '',
            '_attr'  => ['data-screen-map' => implode(',', $screenMap)]
        ], [
            'position'        => 'absolute rounded-1 -z-1 object-cover aspect-13/10',
            // 'left'            => 'left-['.($screenMap[0] * 100).'%]',
            // 'top'             => 'top-['.($screenMap[1] * 100).'%]',
            'left'            => 'left-0',
            'top'             => 'top-0',
            'width'           => 'w-full',
            'transformOrigin' => 'origin-top-left',
        ]);
        $containerComponent->addChild($this->builder->build($screenshotData, $clonedInherited));

        $this->addChild($containerComponent);

        $this->builder->dispatch(new Event('ready-script', 'map-to-screen', file_get_contents(__DIR__ . '/../../js/mapToScreen.js')));

        parent::build($component, $inherited);
    }
}
