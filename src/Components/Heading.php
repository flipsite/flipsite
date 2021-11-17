<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Heading extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\MarkdownTrait;
    protected bool $oneline = true;
    protected string $tag   = 'h2';

    public function build(array $data, array $style, string $appearance) : void
    {
        foreach ($data['flags'] as $flag) {
            if (in_array($flag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                $this->tag = $flag;
            }
        }
        $markdown  = $this->getMarkdownLine((string)$data['text'], $style['text'] ?? null);
        $this->addStyle($style);
        if ('h1' === $this->tag) {
            $this->builder->dispatch(new Event('h1', '', strip_tags($markdown)));
        }
        if (isset($data['name'])) {
            $a = new Element('a');
            $a->setContent($markdown);
            $a->setAttribute('name', $data['name']);
            $this->addChild($a);
        } else {
            $this->setContent($markdown);
        }
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            return ['text' => (string)$data];
        }
        return $data;
    }
}
