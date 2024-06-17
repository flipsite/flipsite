<?php

declare(strict_types=1);
namespace Flipsite\Components;

class Iframe extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'iframe';

    public function build(array $data, array $style, array $options): void
    {
        $this->addStyle($style);
    }
}
