<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Utils\ArrayHelper;

final class Dots extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\AssetsTrait;
    protected string $tag  = 'ol';

    public function build(array $data, array $style, array $options): void
    {
        $this->setAttribute('role', 'list');
        $this->setAttribute('data-dots', true);
        $this->setAttribute('data-target', $data['target'] ?? null);
        $this->addStyle($style);

        if (isset($data['backgrounds'])) {
            $bgStyle = $style['dot']['background'] ?? [];
            unset($style['dot']['background']);

            $bgOptions  = $bgStyle['options'] ?? [];
            $bgOptions['width'] ??= 512;
            $bgOptions['srcset'] ??= ['1x', '2x'];
            $bgOptions['webp'] ??= true;

            $bgStyle['position'] ??= 'bg-center';
            $bgStyle['size'] ??= 'bg-cover';
            $bgStyle['repeat'] ??= 'bg-no-repeat';

            $style['dot'] = ArrayHelper::merge($style['dot'], $bgStyle);

            $backgrounds = ArrayHelper::decodeJsonOrCsv($data['backgrounds']);
            foreach ($backgrounds as &$background) {
                $bgAttributes     = $this->assets->getImageAttributes($background, $bgOptions);
                $background       = $bgAttributes->getSrc();
            }
            $this->setAttribute('data-backgrounds', json_encode($backgrounds));
        }

        $style['dot']['tag'] = 'li';
        $dot                 = $this->builder->build('div', [
            '_noContent' => true,
            '_attr'      => ['role' => 'listitem'],
        ], $style['dot'], $options);

        $this->builder->dispatch(new Event('ready-script', 'toggle', file_get_contents(__DIR__ . '/../../js/ready.dots.js')));

        $this->addChild($dot);
    }
}
