<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Buttons extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $type = 'div';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        if (ArrayHelper::isAssociative($data)) {
            $data = [$data];
        }
        $types = $style['types'] ?? [];
        unset($style['types']);

        foreach ($data as $item) {
            $type = $item['type'] ?? 'primary';
            unset($item['type']);
            $itemStyle = ArrayHelper::merge($style, $types[$type] ?? []);
            $a         = $this->builder->build('a', $item, $itemStyle);
            $this->addChild($a);
        }
    }
}
