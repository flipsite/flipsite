<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Divider extends AbstractComponent
{
    protected string $tag = 'hr';
    protected bool $empty  = true;

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle());
    }
}
