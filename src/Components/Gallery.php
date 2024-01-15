<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Gallery extends AbstractGroup
{
    protected string $tag   = 'div';

    public function normalize(string|int|bool|array $data): array
    {
        $repeat = [];
        if (isset($data['_repeat'])) {
            $list = json_decode($data['_repeat'], true) ?? [];
            foreach ($list as $item) {
                $repeat[] = ['image' => trim($item)];
            }
        }
        unset($data['_repeat']);
        $data = $this->normalizeRepeat($data, $repeat);
        return $data;
    }
}
