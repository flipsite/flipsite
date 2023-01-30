<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractItems extends AbstractComponent
{
    use Traits\BuilderTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        $itemStyle = $style['item'] ?? [];
        unset($style['item']);
        $this->tag ??= $style['tag'];
        unset($style['tag']);
        $this->addStyle($style);

        $children = [];
        $type = $itemStyle['type'];
        unset($itemStyle['type']);
        foreach ($data['items'] as $itemData) {
            $children[] = $this->builder->build($type, $itemData ?? [], $itemStyle, $appearance);
        }
        $this->addChildren($children);
    }
}
