<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Icons extends AbstractGroup
{
    protected string $tag   = 'div';

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        return $data;
    }

    public function build(array $data, array $style, array $options): void
    {
        $total = $data['total'] ?? 5;
        $count = $data['count'] ?? 4;
        $src   = $data['src'] ?? 'zondicons/star.svg';

        unset($data['total'], $data['count'], $data['src']);
        for ($i=0; $i < $total; $i++) {
            $icon                   = [];
            $icon['_style']         = $i < $count ? $style['icon'] : $style['officon'];
            $icon['src']            = $src;
            $data['icon:'.$i]       = $icon;
        }

        parent::build($data, $style, $options);
    }
}
