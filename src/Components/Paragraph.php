<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Paragraph extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\SiteDataTrait;
    use Traits\DateFilterTrait;
    use Traits\PhoneFilterTrait;
    use Traits\UrlFilterTrait;
    use Traits\CheckTextTrait;

    protected string $tag = 'p';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        $data['value'] ??= $data['fallback'] ?? '';
        if (!$data['value']) {
            $this->render = false;
            return;
        }

        if (isset($data['maxCharacters'])) {
            $max = intval($data['maxCharacters']);
            unset($data['maxCharacters']);
            $data['value'] = $this->truncateText($data['value'], $max);
        }

        $data['value'] = $this->checkText($data['value'], 'Paragraph');
        $style         = $component->getStyle();
        $magicLinks    = $data['magicLinks'] ?? false;
        $html          = $this->getMarkdownLine($data['value'] ?? '', ['a', 'strong', 'em', 'code'], $style, $inherited->getAppearance(), $inherited->hasATag(), $magicLinks);

        if (isset($data['formatDate'])) {
            $html = $this->parseDate($html, $data['formatDate']);
        }
        if (isset($data['formatPhone'])) {
            $html = $this->parsePhone($html, $data['formatPhone']);
        }
        if (isset($data['formatUrl'])) {
            $html = $this->parseUrl($html, $data['formatUrl']);
        }
        $this->setContent((string)$html);
    }

    private function truncateText(string $text, int $max): string
    {
        if (mb_strlen($text) > $max) {
            return mb_substr($text, 0, $max) . '...';
        }
        return $text;
    }
}
