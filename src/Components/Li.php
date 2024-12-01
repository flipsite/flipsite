<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Li extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\ClassesTrait;
    use Traits\SiteDataTrait;
    use Traits\BuilderTrait;

    protected string $tag = 'li';

    public function build(array $data, array $style, array $options): void
    {
        if (isset($data['icon'])) {
            $icon = $this->builder->build('icon', $data['icon'], $style['icon'] ?? [], $options);
            unset($data['icon']);
            $this->addChild($icon);
        }
        $html = $this->getMarkdownLine($data['value'] ?? '', $style['value'] ?? [], $options['appearance']);
        $html = $this->addClassesToHtml($html, ['a', 'strong','em','code'], $style, $options['appearance']);

        $text = $this->builder->build('text', ['value' => $html], [], $options);
        $this->addChild($text);
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
