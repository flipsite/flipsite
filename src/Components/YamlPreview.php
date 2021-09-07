<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Symfony\Component\Yaml\Yaml;

final class YamlPreview extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected string $tag = 'code';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $pre = new Pre();
        $pre->addStyle($data->getStyle('pre'));
        $hl = new \Highlight\Highlighter();
        $options = $data->get('options', true);
        $raw = $data->get();

        if (isset($raw['root'])) {
            $root = $raw['root'];
            unset($raw['root']);
            $raw = ArrayHelper::merge($root, $raw);
        }

        if (isset($options['wrap'])) {
            $raw = [$options['wrap'] => $raw];
        }

        $code = Yaml::dump($raw, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $highlighted = $hl->highlight('yaml', $code)->value;

        if (isset($options['indent'])) {
            $rows = explode("\n", trim($highlighted));
            foreach ($rows as $i => &$row) {
                if ($i > 0) {
                    $row = '<span style="font-size:0">'.$options['indent'].'</span>'.$row;
                }
            }
            $highlighted = implode("\n", $rows);
        }




        $pre->setContent($this->addClasses($highlighted, $data->getStyle()));
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
