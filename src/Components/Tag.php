<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Tag extends AbstractComponent
{
    public function __construct(string $tag)
    {
        $this->type = $tag;
    }

    public function build(array $data, array $style, array $flags) : void
    {
        $this->setContent($data['value'] ?? $data);
        $this->addStyle($style);
    }
}
