<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Heading extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\MarkdownTrait;

    public function with(ComponentData $data) : void
    {
        $this->tag = $data->getTag() ?? 'h2';
        $markdown  = $this->getMarkdownLine($data->get('text') ?? $data->get('value'), $data->getStyle('markdown'));
        $this->addStyle($data->getStyle());
        if ('h1' === $this->tag) {
            $this->builder->dispatch(new Event('h1', '', strip_tags($markdown)));
        }
        if ($data->get('name')) {
            $a = new Element('a');
            $a->setContent($markdown);
            $a->setAttribute('name', $data->get('name'));
            $this->addChild($a);
        } else {
            $this->setContent($markdown);
        }
    }
}
