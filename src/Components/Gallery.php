<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Gallery extends AbstractGroup
{
    protected string $tag   = 'div';

    public function normalize(string|int|bool|array $data): array
    {
        $repeat = [];
        foreach (explode(',', $data['_repeat'] ?? '') as $image) {
            $repeat[] = ['image' => trim($image)];
        }
        unset($data['value']);
        $data = $this->normalizeRepeat($data, $repeat);
        return $data;
    }
}
