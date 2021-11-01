<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    protected string $tag = 'p';

    public function with($data, $style) : void
    {
    }

    // public function with(ComponentData $data) : void
    // {
    //     $markdown  = $this->getMarkdownLine($data->get('text') ?? $data->get('value'), $data->getStyle('markdown'));
    //     $this->setContent($markdown);
    //     $this->addStyle($data->getStyle());
    // }
}
