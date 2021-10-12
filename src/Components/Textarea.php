<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Textarea extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'textarea';

    public function with(ComponentData $data) : void
    {
        $flags = $data->getFlags();
        $name  = $data->get('name', true) ?? array_shift($flags);
        $this->setAttribute('name', $name);
        $this->setAttribute('id', $name);
        $this->addStyle($data->getStyle());
        $this->setAttributes($data->get());
    }
}
