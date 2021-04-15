<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Heading extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\MarkdownTrait;

    public function build(array $data, array $style, array $flags) : void
    {
        $this->type = $style['tag'] ?? 'h2';
        $content    = $this->getMarkdownLine($data['value'] ?? $data['text'], $style['markdown'] ?? null);
        $this->addStyle($style);
        if ('h1' === $this->type) {
            $this->builder->dispatch(new Event('h1', '', strip_tags($content)));
        }
        if (isset($data['name']) || ($flags['name'] ?? false)) {
            $name = $data['name'] ?? $this->toKebab($data['value']);
            $a    = new Element('a');
            $a->setContent($content);
            $a->setAttribute('name', $name);
            $a->addStyle($style['name'] ?? []);
            $this->addChild($a);
        } else {
            $this->setContent($content);
        }
    }

    private function toKebab(string $string) : string
    {
        return str_replace(' ', '-', mb_strtolower($string));
    }
}
