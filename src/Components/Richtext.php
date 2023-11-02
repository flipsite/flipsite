<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\StyleAppearanceHelper;

final class Richtext extends AbstractGroup
{
    use Traits\EnvironmentTrait;
    use Traits\BuilderTrait;
    use Traits\UrlTrait;
    protected string $tag       = 'div';

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['value' => (string)$data];
        }
        return $data;
    }

    public function build(array $data, array $style, array $options): void
    {
        if (!$data['value']) {
            return;
        }

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHtml(utf8_decode($data['value']));
        
        // Modify HTML
        $doc = $this->modifyImages($doc, $style['img'] ?? [], $options['appearance']);

        // Render HTML
        $this->content = $doc->saveHtml($doc->getElementsByTagName('body')[0]);
        $this->content = $this->addClasses($this->content, $style, $options['appearance']);
        $this->content = str_replace('<body>','',$this->content);
        $this->content = str_replace("</body>",'',$this->content);
        $this->content = str_replace('</h1>',"</h1>\n",$this->content);
        $this->content = str_replace('</h2>',"</h2>\n",$this->content);
        $this->content = str_replace('</h3>',"</h3>\n",$this->content);
        $this->content = str_replace('</h4>',"</h4>\n",$this->content);
        $this->content = str_replace('</h5>',"</h5>\n",$this->content);
        $this->content = str_replace('</h6>',"</h6>\n",$this->content);
        $this->content = str_replace('</p>',"</p>\n",$this->content);
        $this->content = str_replace('/>',"/>\n",$this->content);
        $this->content = preg_replace('/<span class="ql-cursor">.*?<\/span>/', '', $this->content);
        $this->content = trim($this->content);
        parent::build($data, $style, $options);
    }

    protected function renderContent(int $indentation, int $level, string $content): string
    {
        $i               = str_repeat(' ', $indentation * $level);
        $rows            = explode("\n", $content);
        $renderedContent = '';
        foreach ($rows as $row) {
            $renderedContent .= $i.trim($row)."\n";
        }
        return $renderedContent;
    }

    private function modifyImages(\DOMDocument $doc, array $style, string $appearance): \DOMDocument
    {
        foreach ($doc->getElementsByTagName('img') as $tag) {
            $src = $tag->getAttribute('src');
            if (strpos($src, '@')) {
                $pathinfo = pathinfo($src);
                $tmp = explode('@', $pathinfo['basename']);
                $asset = $tmp[0].'.'.$pathinfo['extension'];
                $img = $this->builder->build('image', ['src' => $asset], $style, ['appearance' => $appearance]);
                foreach ($img->getAttributes() as $attr => $value) {
                    $tag->setAttribute($attr, (string)$value);
                }
            }
        }

        return $doc;
    }

    private function addUrls(string $html): string
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

    private function addClasses(string $html, array $style, string $appearance): string
    {
        $headingBaseStyle = $this->reader->get('theme.components.heading') ?? [];
        $headings     = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        foreach ($headings as $hx) {

            $mergedStyle  = ArrayHelper::merge($headingBaseStyle, $style[$hx] ?? []);
            $headingStyle = StyleAppearanceHelper::apply($mergedStyle, $appearance);
            $headingStyle = array_filter($headingStyle, function($item){ return is_string($item);});
            $html         = str_replace('<'.$hx.'>', '<'.$hx.' class="'.implode(' ', $headingStyle).'">', $html);
        }

        $elements = ['a', 'strong'];
        foreach ($elements as $el) {
            if (isset($style[$el])) {
                $elStyle = StyleAppearanceHelper::apply($style[$el], $appearance);
                $html = str_replace('<'.$el, '<'.$el.' class="'.implode(' ', $elStyle).'"', $html);
            }
        }

        return $html;
    }

    private function getImgClasses(array $style, string $appearance): string
    {
        return implode(' ', StyleAppearanceHelper::apply($style, $appearance));
    }
}
