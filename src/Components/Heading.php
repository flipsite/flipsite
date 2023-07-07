<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Heading extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\GlobalVarsTrait;
    protected bool $oneline = true;
    protected string $tag   = 'h2';

    public function build(array $data, array $style, array $options): void
    {
        $markdown  = $this->getMarkdownLine((string)$data['value'], $style['markdown'] ?? [], $options['appearance']);
        $markdown  = $this->checkGlobalVars($markdown);
        $this->addStyle($style);
        if (isset($data['name'])) {
            $a = new Element('a');
            $a->setContent($markdown);
            $a->setAttribute('name', $data['name']);
            $this->addChild($a);
        } else {
            $this->setContent($markdown);
        }
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['value' => (string)$data];
        }
        return $data;
    }
}
