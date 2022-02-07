<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait MarkdownTrait
{
    use UrlTrait;
    use BuilderTrait;

    private function getMarkdownLine(string $text, array $style, string $appearance) : string
    {
        $parsedown = new \Parsedown();
        $text      = str_replace("\n", ' ', $text);
        $text      = trim($text);
        $html      = $parsedown->line($text);
        if (isset($style['bullet'])) {
            $html = $this->addBullets($html, $style['bullet'], $appearance);
        }
        $html = $this->addMarkdownStyle($html, $style);
        return $this->addUrlsToMarkdown($html);
    }

    private function getMarkdown(string $text, array $style, string $appearance) : string
    {
        $parsedown = new \Parsedown();
        $text      = trim($text);
        $html      = $parsedown->text($text);
        if (isset($style['bullet'])) {
            $html = $this->addBullets($html, $style['bullet'], $appearance);
        }
        $html = $this->addMarkdownStyle($html, $style);
        return $this->addUrlsToMarkdown($html);
    }

    private function addMarkdownStyle(string $html, array $style = []) : string
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
                $html = str_replace('<'.$tag, '<'.$tag.' class="'.$classes.'"', $html);
            }
        }
        return $html;
    }

    private function addUrlsToMarkdown(string $html) : string
    {
        $matches = [];
        preg_match_all('/[ ]{1}href="(.*?)"/', $html, $matches);
        if (0 === count($matches[1])) {
            return $html;
        }
        $hrefs = array_unique($matches[1]);
        foreach ($hrefs as $href) {
            $external = false;
            $newHref  = $this->url($href, $external);
            $html     = str_replace('href="'.$href.'"', 'href="'.$newHref.'"', $html);
        }
        return $html;
    }

    private function addBullets(string $html, array $style, string $appearance) : string
    {
        $icon = $style['icon'];
        unset($style['icon']);
        $bullet                = $this->builder->build('svg', $icon, $style, $appearance);
        $bullet                = str_replace("\n", '', $bullet->render());
        $html                  = str_replace('<li>', '<li>'.$bullet, $html);
        return $html;
    }
}
