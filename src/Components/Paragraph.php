<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    protected string $tag = 'p';

    public function build(array $data, array $style, string $appearance) : void
    {
        $markdown  = $this->getMarkdownLine($data['markdown'] ?? [], $style['markdown'] ?? [], $appearance);
        $this->setContent($markdown);
        $this->addStyle($style);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_string($data)) {
            return ['markdown' => $data];
        }
        return $data;
    }
}
