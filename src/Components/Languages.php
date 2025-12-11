<?php

declare(strict_types=1);
namespace Flipsite\Components;

class Languages extends Nav
{
    public function normalize(array $data): array
    {
        $data['_options'] ??= [];
        $data['_options']['languages'] = true;
        $data['_repeat']               = false;
        return parent::normalize($data);
    }
}
