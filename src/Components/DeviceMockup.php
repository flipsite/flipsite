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
        $this->addStyle($style);
        $deviceScreen          = explode(',', $data['_options']['deviceScreen'] ?? '0,0,1,0,1,1,0,1');
        $container             = new YamlComponentData($component->getPath(), $component->getId(), 'group', [], ['position' => 'relative']);
        $clonedInherited       = clone $inherited;
        $containerComponent    = $this->builder->build($container, $clonedInherited);

        $deviceData  = new YamlComponentData($component->getPath(), $component->getId(), 'image', [
            'value' => $data['value'] ?? '',
            'alt'   => '',
        ], ['maxWidth' => 'max-w-none', 'width' =>  'w-full', 'pointerEvents' => 'pointer-events-none']);
        $device = $this->builder->build($deviceData, $clonedInherited);
        $containerComponent->addChild($device);

        $width  = $device->getAttribute('width') ?? 0;
        $height = $device->getAttribute('height') ?? 0;
        $containerComponent->addCss('aspect-ratio', $width.'/'.$height);

        $screenStyle = [
            'position'        => 'absolute',
            'left'            => 'left-0',
            'top'             => 'top-0',
            'width'           => 'w-full',
            'overflow'        => 'overflow-hidden',
            'transformOrigin' => 'origin-top-left',
            'objectFit'       => 'object-cover',
            'options'         => [],
        ];
        if (isset($data['_options']['screenPosition']) && 'behind' === $data['_options']['screenPosition']) {
            $screenStyle['zIndex'] = '-z-1';
        }

        $screenData  = new YamlComponentData($component->getPath(), $component->getId(), 'group', [
            '_attr'  => ['data-screen-map' => implode(',', $deviceScreen)]
        ], $screenStyle);

        unset($data['value'], $data['_options']);

        $children = $component->getChildren();
        $component->purgeChildren();

        foreach ($children as $child) {
            $screenData->addChild($child);
        }
        $screenComponent = $this->builder->build($screenData, $clonedInherited);
        $screenComponent->addCss('display', 'none');
        $containerComponent->addChild($screenComponent);

        $this->addChild($containerComponent);

        $this->builder->dispatch(new Event('ready-script', 'map-to-screen', file_get_contents(__DIR__ . '/../../js/dist/mapToScreen.min.js')));

        parent::build($component, $inherited);
    }
}
