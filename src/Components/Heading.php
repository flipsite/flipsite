<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Heading extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\ClassesTrait;

    protected bool $oneline = true;
    protected string $tag   = 'h2';

    public function build(array $data, array $style, array $options): void
    {
        $html  = $this->getMarkdownLine($data['value'] ?? '', $style['value'] ?? [], $options['appearance']);
        $html  = $this->addClassesToHtml($html, ['a', 'strong'], $style, $options['appearance']);
        $this->addStyle($style);
        if (isset($data['anchor'])) {
            $a = new Element('a');
            $a->setContent($html);
            $a->setAttribute('href', '#'.$data['anchor']);
            $this->addChild($a);
        } else {
            $this->setContent($html);
        }
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['value' => (string)$data];
        }
        return $data;
    }

    public function getDefaultStyle(): array
    {
        $htmlStyle            = $this->siteData->getHtmlStyle();
        $style                = $htmlStyle['heading'] ?? [];
        $style['font-family'] = 'font-headings';
        return $style;
    }
}
