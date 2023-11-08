<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class SvgPaths extends AbstractComponent
{
    protected string $tag  = 'svg';

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['value' => $data];
        }
        return $data;
    }

    public function build(array $data, array $style, array $options): void
    {
        $this->addStyle($style);
        $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $this->setAttribute('viewBox', $data['viewBox'] ?? '0 0 100 100');
        $this->setAttribute('preserveAspectRatio', 'none');
        $paths = '';
        foreach ($data['paths'] ?? [] as $path) {
            $el = new Element('path',true,true);
            $el->setAttribute('fill','currentColor');
            $el->setAttribute('d',$path['d']);
            $el->setAttribute('opacity',$path['opacity'] ?? '1.0');
            $this->addChild($el);
        }
    }
}
