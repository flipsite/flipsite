<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\MarkdownConverter;
use Flipsite\Utils\StyleAppearanceHelper;
use Flipsite\Components\Element;

trait MarkdownTrait
{
    use ActionTrait;
    use BuilderTrait;

    private function getMarkdownLine(string $markdown, array $tags, array $style, string $appearance, bool $removeLinks = false, bool $magicLinks = false): string
    {
        if ($magicLinks) {
            $markdown = $this->urlsToLinks($markdown);
        }
        $markdown    = $this->fixInvalidMarkdown($markdown);

        $config      = [];
        $environment = new Environment($config);

        $environment->addExtension(new InlinesOnlyExtension());

        // Instantiate the converter engine and start converting some Markdown!
        $converter = new MarkdownConverter($environment);

        $html = (string)$converter->convert($markdown);
        $html = preg_replace('/^<p>(.*)<\/p>$/s', '$1', trim($html));

        if ($removeLinks) {
            $html          = str_replace('<a href="', '<span data-href="', $html);
            $html          = str_replace('</a>', '</span>', $html);
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

    private function urlsToLinks(string $text): string
    {
        $placeholders = [];
        $text         = preg_replace_callback(
            '/\[[^\]]+\]\([^\)]+\)/',
            function ($match) use (&$placeholders) {
                $placeholder                = '__PLACEHOLDER_' . count($placeholders) . '__';
                $placeholders[$placeholder] = $match[0]; // Store original markdown link
                return $placeholder;
            },
            $text
        );

        $text = preg_replace(
            '/(?<!\[)(?<!\()\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b(?!\))/',
            '[$1](mailto:$1)',
            $text
        );

        $text = preg_replace(
            '/(?<!\[)(?<!\]\()(?<!mailto:)(?<!tel:)(\+\d{7,15})(?!\))/i',
            '[$1](tel:$1)',
            $text
        );

        $text = preg_replace(
            '/(?<!\[)\b(https?:\/\/[^\s<]+[^\s.,;:<])\b(?!\))/',
            '[$1]($1)',
            $text
        );

        // Step 4: Restore original markdown links from placeholders
        $text = str_replace(array_keys($placeholders), array_values($placeholders), $text);

        return $text;
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

    private function fixInvalidMarkdown(string $markdown): string
    {
        $markdown = preg_replace('/\s*\*\*\s*([^*]+?)\s*\*\*\s+/', ' **$1** ', $markdown);
        $markdown = str_replace('**', '___BOLD___', $markdown);
        $markdown = preg_replace('/\s*\*\s*([^*]+?)\s*\*\s+/', ' *$1* ', $markdown);
        $markdown = str_replace('___BOLD___', '**', $markdown);
        $markdown = preg_replace('/\s*`\s*([^`]+?)\s*`\s+/', ' `$1` ', $markdown);
        return preg_replace('/\s+/', ' ', trim($markdown));
    }
}
