<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Tag extends AbstractComponent
{
    use Traits\MarkdownTrait;

    public function __construct(string $tag = 'div')
    {
        $this->tag = $tag;
    }

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->tag = $data['tag'] ?? $this->tag;
        $this->setContent($data['value']);
        $this->addStyle($style);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_string($data)) {
            return ['value' => $data];
        }
        return $data;
    }
}
