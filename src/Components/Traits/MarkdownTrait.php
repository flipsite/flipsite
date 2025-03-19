<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use Flipsite\Utils\StyleAppearanceHelper;
use Flipsite\Components\Element;

trait MarkdownTrait
{
    use ActionTrait;
    use BuilderTrait;


    private function getMarkdownLine(string $markdown, array $tags, array $style, string $appearance, bool $removeLinks = false): string
    {
        $markdown = $this->emailsToLinks($markdown);
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new InlinesOnlyExtension());

        $converter = new CommonMarkConverter([], $environment);
        $html = (string)$converter->convert($markdown);
        $html = preg_replace('/^<p>(.*)<\/p>$/s', '$1', trim($html));

        if ($removeLinks) {
            $html = str_replace('<a href="', '<span data-href="', $html);
            $html = str_replace('</a>', '</span>', $html);
            $style['span'] = $style['a'] ?? [];
            unset($style['a']);
            $tags[] = 'span';
        }

        $html = $this->addClassesToHtml($html, $tags, $style, $appearance);

        $html = $this->fixUrlsInHtml($html);
        return $html;
    }

    private function emailsToLinks(string $markdown): string
    {
        return preg_replace(
            '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
            '[$1](mailto:$1)',
            $markdown
        );
    }

    private function addClassesToHtml(string $html, array $tags, array $style, string $appearance): string
    {
        foreach ($tags as $tag) {
            if (isset($style[$tag])) {
                $tagStyle = StyleAppearanceHelper::apply($style[$tag], $appearance);
                if (!$tagStyle) {
                    continue;
                }
                $element = new Element('div');
                if (isset($tagStyle['background'])) {
                    $this->builder->handleBackground($element, $tagStyle['background']);
                    unset($tagStyle['background']);
                }
                $element->addStyle($tagStyle);
                $attributes = $element->renderAttributes();
                $html       = str_replace('<' . $tag, '<' . $tag . $attributes, $html);
            }
        }
        return $html;
    }
}
