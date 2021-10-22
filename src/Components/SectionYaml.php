<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Symfony\Component\Yaml\Yaml;

class SectionYaml extends AbstractComponent
{
    use Traits\SectionBuilderTrait;
    protected string $tag = 'code';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        if ($data->get('section')) {
            $raw          = $this->sectionBuilder->getExample($data->get('section'));
            $raw['style'] = $data->get('section');
        } else {
            $raw = $this->sectionBuilder->getExampleStyle($data->get('style'));
        }

        $hl          = new \Highlight\Highlighter();
        $options     = $data->get('options', true);
        $code        = Yaml::dump($raw, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $highlighted = $hl->highlight('yaml', $code)->value;

        $pre = new Pre();
        $pre->addStyle($data->getStyle('pre'));
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
