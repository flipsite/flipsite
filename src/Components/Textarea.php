<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Textarea extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'textarea';

    public function build(array $data, array $style, string $appearance) : void
    {
        $name  = $data['name'] ?? array_shift($data['flags']);
        $this->setAttribute('name', $name);
        $this->setAttribute('id', $name);
        $this->addStyle($style);
    }
}
