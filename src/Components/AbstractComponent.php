<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractComponent extends AbstractElement
{
    abstract public function build(array $data, array $style, array $options) : void;

    public function normalize(string|int|bool|array $data) : array
    {
        return is_array($data) ? $data : ['value' => $data];
    }
}
