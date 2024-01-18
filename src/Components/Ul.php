<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Ul extends AbstractGroup
{
    protected string $tag   = 'ul';

    public function normalize(string|int|bool|array $data): array
    {
        $repeat = [];
        if (isset($data['_repeat'])) {
            $list = ArrayHelper::decodeJsonOrCsv($data['_repeat']);
            foreach ($list as $item) {
                $repeat[] = ['item' => trim($item)];
            }
        }
        unset($data['_repeat']);
        $data = $this->normalizeRepeat($data, $repeat);
        return $data;
    }
}
