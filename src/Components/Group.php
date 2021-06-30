<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Group extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $tag = 'div';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $components = $this->builder->build($data->get(), $data->getStyle(), $data->getAppearance());
        $this->addChildren($components);
    }
}
