<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Ul extends AbstractGroup
{
    protected string $tag   = 'ul';

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        $repeat = [];
        if (isset($data['_repeat']) && is_string($data['_repeat'])) {
            $list = ArrayHelper::decodeJsonOrCsv($data['_repeat']);
            foreach ($list as $item) {
                $repeat[] = ['item' => trim($item)];
            }
        } elseif (isset($data['_repeat']) && is_array($data['_repeat'])) {
            foreach ($data['_repeat'] as $item) {
                $repeat[] = ['item' => $item];
            }
        }
        unset($data['_repeat']);
        $data = $this->normalizeRepeat($data, $repeat);
        return $data;
    }
}
