<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use Flipsite\Utils\ArrayHelper;

trait MarkdownTrait
{
    use ActionTrait;
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
        $html = $this->addMarkdownStyle($html, $style, $appearance);
        return $this->addUrlsToMarkdown($html);
    }

    private function getMarkdown(string $text, array $style, string $appearance) : string
    {
        $parsedown = new \Parsedown();
        $text      = trim($text);
        $html      = $parsedown->text($text);
        if (!$html) {
            return $html;
        }
        $html      = str_replace('<li>', '  <li>', $html);

        if (isset($style['bullet'])) {
            $html = $this->addBullets($html, $style['bullet'], $appearance);
        }
        $html = $this->addMarkdownImages($html, $style['img'] ?? [], $appearance);
        $html = $this->addMarkdownStyle($html, $style, $appearance);
        $html = $this->addUrlsToMarkdown($html);
        return $html;
    }

    private function addMarkdownImages(string $text, array $imageStyle, string $appearance) : string
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHtml($text);
        $images = [];
        foreach ($doc->getElementsByTagName('img') as $tag) {
            $images[] = $tag->getAttribute('src');
        }
        $images = array_unique($images);
        foreach ($images as $src) {
            $img  = $this->builder->build('image', ['src' => $src], $imageStyle, $appearance)->render();
            $img  = str_replace('<img', '', $img);
            $img  = str_replace('>', '', $img);
            $img  = str_replace('alt="" ', '', $img);
            $img  = trim($img);
            $text = str_replace('src="'.$src.'"', $img, $text);
        }

        return $text;
    }

    private function addMarkdownStyle(string $html, array $style = [], string $appearance = 'light') : string
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
                if ($tag === 'img') {
                    continue;
                }
                $dark = [];
                if (isset($classes['dark'])) {
                    $dark = $classes['dark'];
                    unset($classes['dark']);
                }
                if (is_array($classes)) {
                    if ('dark' === $appearance) {
                        $classes = ArrayHelper::merge($classes, $dark);
                    }
                    $classes = implode(' ', $classes);
                }
                $matches = [];
                preg_match_all('/<'.$tag.'[\>\s]{1}/', $html, $matches);
                $tags = array_unique($matches[0]);
                foreach ($tags as $t) {
                    $hasAttr = str_ends_with($t, ' ');
                    $t       = str_replace('<', '', $t);
                    $t       = str_replace('>', '', $t);
                    $t       = trim($t);

                    if ($hasAttr) {
                        $html = str_replace('<'.$t.' ', '<'.$t.' class="'.$classes.'" ', $html);
                    } else {
                        $html = str_replace('<'.$t.'>', '<'.$t.' class="'.$classes.'">', $html);
                    }
                }
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
            if ($external) {
                $html = str_replace('href="'.$href.'"', 'href="'.$newHref.'" target="_blank" rel="noopener noreferrer"', $html);
            } else {
                $html = str_replace('href="'.$href.'"', 'href="'.$newHref.'"', $html);
            }
        }
        return $html;
    }

    private function addBullets(string $html, array $style, string $appearance) : string
    {
        $icon = $style['icon'];
        unset($style['icon']);
        $bullet                = $this->builder->build('svg', $icon, $style, $appearance);
        $bullet                = str_replace("\n", '', $bullet->render());
        $html                  = str_replace('<li>', "<li>\n    ".$bullet."\n    ", $html);
        $html                  = str_replace("</li>\n", "\n  </li>\n", $html);
        return $html;
    }
}
