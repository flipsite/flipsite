<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\ClassesTrait;
    use Traits\SiteDataTrait;

    protected string $tag = 'p';

    public function build(array $data, array $style, array $options): void
    {
        $html  = $this->getMarkdownLine($data['value'] ?? '', $style['value'] ?? [], $options['appearance']);
        $html  = $this->addClassesToHtml($html, ['a', 'strong'], $style, $options['appearance']);
        $this->setContent((string)$html);
        $this->addStyle($style);
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (is_string($data)) {
            return ['value' => $data];
        }
        return $data;
    }

    public function getDefaultStyle(): array
    {
        $style = [];
        $bodyStyle = $this->siteData->getBodyStyle();
        if (isset($bodyStyle['textColor'])) {
            $style['textColor'] = $bodyStyle['textColor'];
        }
        if (isset($bodyStyle['dark']['textColor'])) {
            $style['dark'] = [];
            $style['dark']['textColor'] = $bodyStyle['dark']['textColor'];
        }
        return $style;
    }
}
