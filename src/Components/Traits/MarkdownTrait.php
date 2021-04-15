<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

trait MarkdownTrait
{
    use UrlTrait;

    private function getMarkdownLine(string $text, ?array $style = []) : string
    {
        $parsedown = new \Parsedown();
        $text      = str_replace("\n", ' ', $text);
        $text      = trim($text);
        $html      = $this->addMarkdownStyle($parsedown->line($text), $style);
        return $this->addUrlsToMarkdown($html);
    }

    private function getMarkdown(string $text, ?array $style = []) : string
    {
        $parsedown = new \Parsedown();
        $text      = trim($text);
        $html      = $this->addMarkdownStyle($parsedown->text($text), $style);
        return $this->addUrlsToMarkdown($html);
    }

    private function addMarkdownStyle(string $html, ?array $style = null) : string
    {
        if (isset($style['tableWrap'])) {
            $classes = implode(' ', $style['tableWrap']);
            $html    = str_replace('<table>', '<div class="'.$classes.'"><table>', $html);
            $html    = str_replace('</table>', '</table></div>', $html);
            unset($style['tableWrap']);
        }
        $html = str_replace('<pre><code>', '<pre>', $html);
        $html = str_replace('</code></pre>', '</pre>', $html);
        if (is_array($style) && count($style)) {
            foreach ($style as $tag => $classes) {
                if (is_array($classes)) {
                    $classes = implode(' ', $classes);
                }
                $html = str_replace('<'.$tag.'>', '<'.$tag.' class="'.$classes.'">', $html);
                $html = str_replace('<'.$tag.' ', '<'.$tag.' class="'.$classes.'"', $html);
            }
        }
        return $html;
    }

    private function addUrlsToMarkdown(string $html) : string
    {
        $matches = [];
        preg_match_all('/\s{1}href=(["\'])(.*?)\1/', $html, $matches);
        $hrefs = array_unique($matches[2]);
        foreach ($hrefs as $href) {
            $external = false;
            $newHref  = $this->url($href, $external);
            $html     = str_replace(' href="'.$href.'"', ' href="'.$newHref.'"', $html);
        }
        return $html;
    }
}
