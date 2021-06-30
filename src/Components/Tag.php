<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Tag extends AbstractComponent
{
    public function __construct(string $tag)
    {
        $this->tag = $tag;
    }

    public function with(ComponentData $data) : void
    {
        $this->setContent($data->get('value'));
        $this->addStyle($data->getStyle());
    }
}
