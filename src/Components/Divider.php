<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Divider extends AbstractComponent
{
    protected string $type = 'hr';
    protected bool $empty  = true;

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style);
    }
}
