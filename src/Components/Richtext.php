<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\StyleAppearanceHelper;

final class Richtext extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\ActionTrait;
    protected string $tag = 'div';

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
        $html = mb_convert_encoding($data['value'], 'HTML-ENTITIES', 'UTF-8');
        $html = $this->convertPreToHtml($html);

        if ($data['magicLinks'] ?? false) {
            // Mailto
            $html = preg_replace("/([A-z0-9\._-]+\@[A-z0-9_-]+\.)([A-z0-9\_\-\.]{1,}[A-z])/", '<a href="mailto:$1$2">$1$2</a>', $html);
            $html = preg_replace('/(?:http|ftp)s?:\/\/(?:www\.)?([a-z0-9.-]+\.[a-z]{2,5}(?:\/\S*)?)/', '<a href="$1" rel="noopener noreferrer" target="_blank">$1</a>', $html);
        } else {
            $html = $this->fixUrlsInHtml($html);
        }

        $doc->loadHtml($html);

        // Modify HTML
        $doc = $this->modifyImages($doc, $style['img'] ?? [], $options['appearance']);

        // Render HTML
        $this->content = $doc->saveHtml($doc->getElementsByTagName('body')[0]);

        $pattern = '/ style\s*=\s*["\'][^"\']*?["\']/i';

        // Remove the style attribute using preg_replace
        $this->content = preg_replace($pattern, '', $this->content);

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
        if ($data['removeEmptyLines'] ?? false) {
            $this->content = str_replace("<p><br></p>\n", '', $this->content);
        }
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

    private function addClasses(string $html, array $style, string $appearance): string
    {
        $headingBaseStyle = $this->siteData->getComponentStyle('heading') ?? [];
        $headings     = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        foreach ($headings as $hx) {
            $mergedStyle  = ArrayHelper::merge($headingBaseStyle, $style[$hx] ?? []);
            $headingStyle = StyleAppearanceHelper::apply($mergedStyle, $appearance);
            $headingStyle = array_filter($headingStyle, function($item){ return is_string($item);});
            $html         = str_replace('<'.$hx.'>', '<'.$hx.' class="'.implode(' ', $headingStyle).'">', $html);
        }

        $elements = ['a', 'img', 'strong','table','tbl','td','tr','th','ul','ol','li'];
        foreach ($elements as $el) {
            if (isset($style[$el])) {
                $tag = $el === 'tbl' ? 'table' : $el;
                $elStyle = StyleAppearanceHelper::apply($style[$el], $appearance);
                $html = str_replace('<'.$tag, '<'.$tag.' class="'.implode(' ', $elStyle).'"', $html);
            }
        }

        return $html;
    }

    private function getImgClasses(array $style, string $appearance): string
    {
        return implode(' ', StyleAppearanceHelper::apply($style, $appearance));
    }

    private function convertPreToHtml(string $html):string {
        $matches = [];
        preg_match_all('/<pre class="ql-syntax" spellcheck="false">((.|\n)*?)<\/pre>/', $html, $matches);

        foreach ($matches[1] as $i => $match) {
            $match = str_replace('&nbsp;','',$match);
            $match = str_replace('&lt;','<',$match);
            $match = str_replace('&gt;','>',$match);
            $html = str_replace($matches[0][$i], $match, $html);

        }
        return $html;
    }
}
