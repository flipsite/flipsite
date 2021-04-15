<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    protected string $type = 'p';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->setContent($this->getMarkdownLine($data['value'] ?? $data, $style['markdown'] ?? null));
        $this->addStyle($style);
    }
}
