<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Label extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'label';

    public function with(ComponentData $data) : void
    {
        $for = $data->get('for', true) ?? $data->getFlags()[0];
        $this->addStyle($data->getStyle());
        if (null !== $for) {
            $this->setAttribute('for', $for);
        }
        $this->setContent($data->get('value', true));
    }
}
