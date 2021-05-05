<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Heading extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\MarkdownTrait;

    public function build(array $data, array $style, array $flags) : void
    {
        $tag  = null;
        $tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        foreach ($flags as $flag) {
            if (in_array($flag, $tags)) {
                $tag = $flag;
            }
        }
        $this->type = $data['tag'] ?? $tag ?? 'h2';

        $content = $this->getMarkdownLine($data['value'] ?? $data['text'], $style['markdown'] ?? null);
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
