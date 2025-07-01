<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait UrlFilterTrait
{
    protected function parseUrl(string $html, string $format = 'full'): string
    {
        if ('full' === $format) {
            return $html;
        }

        // Load HTML into DOM
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true); // Suppress HTML5 warnings
        $dom->loadHTML(mb_convert_encoding('<html><body><p>'.$html.'</p></body></html>', 'HTML-ENTITIES', 'UTF-8'));

        // Walk through all text nodes
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//text()') as $textNode) {
            /** @var \DOMText $textNode */
            $originalText = $textNode->nodeValue;

            // Replace raw phone numbers in text only
            $formattedText = preg_replace_callback(
                '/\bhttps?:\/\/[^\s<>"\'()]+/i',
                function ($matches) use ($format) {
                    return $this->convertToUrlFormat($matches[0], $format);
                },
                $originalText
            );

            if ($formattedText !== $originalText) {
                $textNode->nodeValue = $formattedText;
            }
        }

        $xpath = new \DOMXPath($dom);
        $p     = $xpath->query('//body//p')->item(0);

        if ($p) {
            $innerHtml = '';
            foreach ($p->childNodes as $child) {
                $innerHtml .= $dom->saveHTML($child);
            }
            return $innerHtml;
        }

        return $innerHTML;
    }

    protected function convertToUrlFormat(string $url, string $format): string
    {
        switch ($format) {
            case 'short':
                // Example: Convert to a shortened URL format
                return preg_replace('/^https?:\/\//', '', $url);
            case 'domain':
                // Example: Extract domain from URL
                return parse_url($url, PHP_URL_HOST) ?: $url;
            default:
                return $url; // Fallback to original URL if format is unknown
        }
    }
}
