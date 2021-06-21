<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Group extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $type = 'div';

    public function build(array $data, array $style, array $flags, string $appearance = 'light') : void
    {
        $this->addStyle($style['container'] ?? []);
        $components = $this->builder->buildGroup($data, $style, $appearance);
        $this->addChildren($components);
    }
}
