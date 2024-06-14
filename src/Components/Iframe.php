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
        if (isset($data['value'])) {
            $this->setAttribute('value', $data['value']);
        }
        if (isset($data['title'])) {
            $this->setAttribute('title', $data['title']);
        }
    }
}
