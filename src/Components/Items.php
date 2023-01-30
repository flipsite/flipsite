<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Items extends AbstractItems
{
    public function normalize(string|int|bool|array $data): array
    {
        if (!ArrayHelper::isAssociative($data)) {
            $data['items'] = $data;
        }
        return $data;
    }
}
