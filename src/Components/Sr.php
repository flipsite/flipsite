<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Sr extends AbstractComponent
{
    protected string $type  = 'span';
    protected bool $oneline = true;

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle(['screenReaders' => 'sr-only']);
        $this->setContent($data['text'] ?? $data['value']);
    }
}
