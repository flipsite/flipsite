<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Items extends AbstractItems
{
    public function normalize(string|int|bool|array $data): array
    {
        if (isset($data['items'])) {
            return $data;
        }
        $items = array_filter($data, function ($key) {
            return is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        $_ = array_filter($data, function ($key) {
            return !is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);

        return array_merge($_, ['items'=>$items]);
    }
}
