<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Ul extends AbstractGroup
{
    protected string $tag   = 'ul';

    public function normalize(string|int|bool|array $data): array
    {
        $repeat = [];
        foreach(explode(',', $data['value']) as $item) {
            $repeat[] = ['item' => trim($item)];
        }
        unset($data['value']);
        $data = $this->normalizeRepeat($data, $repeat);
        return $data;
    }
}
