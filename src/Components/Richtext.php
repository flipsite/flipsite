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

    public function build(array $data, array $style, array $options) : void
    {
        if (!$data['value']) {
            return;
        }
        if (strpos($data['value'], '{"ops":') === false) {
            $data['value'] = '{"ops":[{"insert":"'.(string)$data['value'].'\n"}]}';
        }

        $image          = new \nadar\quill\listener\Image();
        $image->wrapper = '<img src="{src}" class="'.$this->getImgClasses($style['img'] ?? [], $options['appearance']).'" />';

        $lexer         = new \nadar\quill\Lexer($data['value']);
        $lexer->registerListener($image);

        $this->content = trim($lexer->render());
        $this->content = $this->addClasses($this->content, $style, $options['appearance']);
        $this->content = $this->addCorrectImagePaths($this->content, $style['img'] ?? [], $options['appearance']);
        $this->content = $this->addUrls($this->content);

        // remove empty lines
        $this->content = str_replace("<p><br></p>\n", '', $this->content);

        parent::build($data, $style, $options);
    }

    protected function renderContent(int $indentation, int $level, string $content) : string
    {
        $i               = str_repeat(' ', $indentation * $level);
        $rows            = explode("\n", $content);
        $renderedContent = '';
        foreach ($rows as $row) {
            $renderedContent .= $i.trim($row)."\n";
        }
        return $renderedContent;
    }

    private function addCorrectImagePaths(string $html, array $style, string $appearance) : string {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHtml($html);
        $images = [];
        foreach ($doc->getElementsByTagName('img') as $tag) {
            $src = $tag->getAttribute('src');
            if (strpos($src,'@')) {
                $images[] = $src;
            }
        }
        $images = array_unique($images);
        foreach ($images as $src) {
            $pathinfo = pathinfo($src);
            $tmp = explode('@',$pathinfo['basename']);
            $asset = $tmp[0].'.'.$pathinfo['extension'];
            
            $img  = $this->builder->build('image', ['src' => $asset], ['options' => $style['options'] ?? []], ['appearance' => $appearance])->render();
            $img  = str_replace('<img', '', $img);
            $img  = str_replace('>', '', $img);
            $img  = str_replace('alt="" ', '', $img);
            $img  = trim($img);
            $html = str_replace('src="'.$src.'"', $img, $html);
        }
        return $html;
    }

    private function addUrls(string $html) : string
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

    private function addClasses(string $html, array $style, string $appearance) : string
    {
        $headingStyle = $this->reader->get('theme.components.heading') ?? [];
        $headings     = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        foreach ($headings as $hx) {
            $mergedStyle  = ArrayHelper::merge($headingStyle, $style[$hx] ?? []);
            $headingStyle = StyleAppearanceHelper::apply($mergedStyle, $appearance);
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

    private function getImgClasses(array $style, string $appearance) : string
    {
        return implode(' ', StyleAppearanceHelper::apply($style, $appearance));
    }
}
