<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Rating extends AbstractComponent
{
    protected string $tag   = 'div';
    use Traits\BuilderTrait;

    public function build(array $data, array $style, string $appearance): void
    {
        $starData = [];
        $this->addStyle($style);
        $children = [];
        $itemStyle = $style['item'] ?? [];
        $activeStyle = ArrayHelper::merge($itemStyle , $style['active'] ?? []);
        for ($i=0; $i < $data['max']; $i++) {
            $children[] = $this->builder->build('icon', ['value'=>$data['icon']], $data['value'] > $i ? $activeStyle : $itemStyle, $appearance);
        }
        $this->addChildren($children);
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => (int)$data];
        }
        $data['max'] ??= 5;
        $data['icon'] ??= 'zondicons/star-full.svg';
        return $data;
    }
}
