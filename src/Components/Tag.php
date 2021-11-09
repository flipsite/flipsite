<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Tag extends AbstractComponent
{
    use Traits\MarkdownTrait;
    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->setContent($data['value']);
        $this->addStyle($style);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_string($data)) {
            return ['value' => $data];
        }
    }
}
