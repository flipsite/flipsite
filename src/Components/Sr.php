<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Sr extends AbstractComponent
{
    protected string $tag   = 'span';
    protected bool $oneline = true;

    public function with(ComponentData $data) : void
    {
        $this->addStyle(['screenReaders' => 'sr-only']);
        $this->setContent($data->get('text') ?? $data->get('value'));
    }
}
