<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Script extends AbstractComponent
{
    protected string $tag   = 'script';
    protected bool $oneline = true;

    public function normalize(string|int|bool|array $data) : array
    {
        return $data;
    }

    public function build(array $data, array $style, array $options) : void
    {
    }
}
