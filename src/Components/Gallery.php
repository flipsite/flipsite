<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Gallery extends AbstractGroup
{
    protected string $tag   = 'div';

    public function normalize(array $data): array
    {
        $repeat = [];
        if (isset($data['_repeat'])) {
            $list = ArrayHelper::decodeJsonOrCsv($data['_repeat']);
            foreach ($list as $item) {
                $repeat[] = ['image' => trim($item)];
            }
        }
        unset($data['_repeat']);
        return $this->normalizeRepeat($data, $repeat);
    }

}
