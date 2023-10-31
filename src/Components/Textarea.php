<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Textarea extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'textarea';

    public function build(array $data, array $style, array $options) : void
    {
        $this->addStyle($style);
        $this->setContent($data['value']);
    }
}
