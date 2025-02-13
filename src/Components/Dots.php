<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use Flipsite\Data\YamlComponentData;

final class Dots extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\AssetsTrait;
    protected string $tag  = 'ol';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $style = $component->getStyle();
        $this->setAttribute('role', 'list');
        $this->setAttribute('data-dots', true);
        $this->setAttribute('data-target', $data['target'] ?? null);

        // if ($data['backgrounds'] ?? false) {
        //     $bgStyle = $style['dot']['background'] ?? [];
        //     unset($style['dot']['background']);

        //     $bgOptions  = $bgStyle['options'] ?? [];
        //     $bgOptions['width'] ??= 512;
        //     $bgOptions['srcset'] ??= ['1x', '2x'];
        //     $bgOptions['webp'] ??= true;

        //     $bgStyle['position'] ??= 'bg-center';
        //     $bgStyle['size'] ??= 'bg-cover';
        //     $bgStyle['repeat'] ??= 'bg-no-repeat';

        //     $style['dot'] = ArrayHelper::merge($style['dot'] ?? [], $bgStyle);

        //     $backgrounds = [];
        //     $key         = $data['cmsField'] ?? 'image';
        //     foreach ($this->builder->getSharedData($data['target']) as $item) {
        //         $image            = $item[$key] ?? $item['image'] ?? '';
        //         $bgAttributes     = $this->assets->getImageAttributes($image, $bgOptions);
        //         if ($bgAttributes) {
        //             $backgrounds[]    = $bgAttributes->getSrc();
        //         }
        //     }
        //     $this->setAttribute('data-backgrounds', json_encode($backgrounds));
        // }

        $clonedInherited = clone $inherited;
        $clonedInherited->setParent($component->getId(), $component->getType());
        $dotComponentData = new YamlComponentData($component->getId(), null, 'div', [
            '_noContent' => true,
            '_attr'      => ['role' => 'listitem'],
            '_style'     => $style['dot'] ?? [],
        ]);
        $dot = $this->builder->build($dotComponentData, $clonedInherited);

        $this->builder->dispatch(new Event('ready-script', 'toggle', file_get_contents(__DIR__ . '/../../js/dist/dots.min.js')));

        $this->addChild($dot);
    }
}
