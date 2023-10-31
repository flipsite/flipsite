<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Input extends AbstractComponent
{
    protected bool $oneline = true;
    protected bool $empty   = true;
    protected string $tag   = 'input';

    public function build(array $data, array $style, array $options) : void
    {
        $this->addStyle($style);
    }
}
