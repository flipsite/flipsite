<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\GlobalVarsTrait;
    protected string $tag = 'p';

    public function build(array $data, array $style, array $options): void
    {
        $markdown = $this->getMarkdownLine($data['value'] ?? '', $style['value'] ?? [], $options['appearance']);
        $markdown = $this->checkGlobalVars($markdown);
        $this->setContent((string)$markdown);
        $this->addStyle($style);
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (is_string($data)) {
            return ['value' => $data];
        }
        return $data;
    }
}
