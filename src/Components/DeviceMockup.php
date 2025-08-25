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
        $data                  = $component->getData();
        $style                 = $component->getStyle();
        $deviceScreen          = explode(',', $data['deviceScreen'] ?? '0,0,1,0,1,1,0,1');
        $container             = new YamlComponentData($component->getPath(), $component->getId().'.container', 'group', [], ['position' => 'relative']);
        $clonedInherited       = clone $inherited;
        $containerComponent    = $this->builder->build($container, $clonedInherited);

        $deviceData  = new YamlComponentData($component->getPath(), $component->getId().'.device', 'image', [
            'value' => $data['device'] ?? '',
            'alt'   => '',
        ], ['maxWidth' => 'max-w-none', 'width' =>  'w-full', 'pointerEvents' => 'pointer-events-none']);
        $device = $this->builder->build($deviceData, $clonedInherited);
        $containerComponent->addChild($device);

        $screenStyle = [
            'position'        => 'absolute',
            'left'            => 'left-0',
            'top'             => 'top-0',
            'width'           => 'w-full',
            'overflow'        => 'overflow-hidden',
            'transformOrigin' => 'origin-top-left',
            'objectFit'       => 'object-cover',
            'options'         => []
        ];
        if (isset($data['screenPosition']) && 'behind' === $data['screenPosition']) {
            $screenStyle['zIndex'] = '-z-1';
        }
        if (isset($style['screenCorners'])) {
            $screenStyle['borderRadius'] = $style['screenCorners'];
            $component->removeStyleValue('screenCorners');
        }
        if (isset($style['screenCorners'])) {
            $screenStyle['borderRadius'] = $style['screenCorners'];
            $component->removeStyleValue('screenCorners');
        }
        if (isset($style['screenAspectRatio'])) {
            $screenStyle['options']['width']       = 512;
            $screenStyle['options']['aspectRatio'] = $style['screenAspectRatio'];
            $component->removeStyleValue('screenAspectRatio');
        }
        $screenData  = new YamlComponentData($component->getPath(), $component->getId(), 'group', [
            '_attr'  => ['data-screen-map' => implode(',', $deviceScreen)]
        ], $screenStyle);

        $children = $component->getChildren();
        $component->purgeChildren();

        foreach ($children as $child) {
            $screenData->addChild($child);
        }

        $containerComponent->addChild($this->builder->build($screenData, $clonedInherited));

        $this->addChild($containerComponent);

        $this->builder->dispatch(new Event('ready-script', 'map-to-screen', file_get_contents(__DIR__ . '/../../js/mapToScreen.js')));

        parent::build($component, $inherited);
    }
}
