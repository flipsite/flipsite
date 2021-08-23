<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Symfony\Component\Yaml\Yaml;

final class Code extends AbstractComponent
{
    protected string $tag = 'code';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $pre = new Pre();
        $pre->addStyle($data->getStyle('pre'));
        $hl = new \Highlight\Highlighter();
        $code = Yaml::dump($data->get(), 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $highlighted = $hl->highlight($data->getFlags()[0], $code);
        $pre->setContent($this->addClasses($highlighted->value, $data->getStyle()));
        $this->addChild($pre);
    }

    private function addClasses(string $html, array $style) : string
    {
        if (is_array($style) && count($style)) {
            foreach ($style as $class => $classes) {
                if (is_array($classes)) {
                    $classes = implode(' ', $classes);
                }
                $html = str_replace('class="hljs-'.$class.'"', 'class="'.$classes.'"', $html);
            }
        }

        return $html;
    }
}
