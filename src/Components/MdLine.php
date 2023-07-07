<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class MdLine extends AbstractComponent
{
    use Traits\MarkdownTrait;
    protected string $type = '';

    public function build(array $data, array $style, array $options) : void
    {
        $this->content = $this->getMarkdownLine($data['value'], $style ?? null, $options);
    }
}
