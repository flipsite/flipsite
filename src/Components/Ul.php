<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Ul extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected string $type = 'ul';

    public function build(array $data, array $style) : void
    {
        $this->addStyle($style['container'] ?? []);
        foreach ($data as $item) {
            $li = $this->builder->build('li', $item, $style);
            $this->addChild($li);
        }
    }

    public function normalize($data) : array
    {
        if (ArrayHelper::isAssociative($data)) {
            return [$data];
        }
        return $data;
    }
}
