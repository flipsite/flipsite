<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\StyleAppearanceHelper;

final class Md extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\EnviromentTrait;
    use Traits\BuilderTrait;
    protected string $type = 'div';

    private ?Element $container = null;

    public function with(ComponentData $data) : void
    {
        $value = $data->get('value');
        if (mb_strpos($value, '.md')) {
            $filename = $this->enviroment->getSiteDir().'/'.$value;
            if (file_exists($filename)) {
                $markdown = file_get_contents($filename);
            }
        } else {
            $markdown = $value;
        }
        $containerStyle = $data->getStyle('container');
        $style          = $data->getStyle();
        unset($style['container']);
        $style          = $this->extendStyle($style, $data->getAppearance());

        $this->content  = $this->getMarkdown($markdown ?? '', $style ?? null);
        $containerStyle = $data->getStyle('container');
        if ($containerStyle) {
            $this->container = new Element($containerStyle['type'] ?? 'div');
            unset($containerStyle['type']);
            $this->container->addStyle($containerStyle);
        }
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i    = str_repeat(' ', $indentation * $level);
        if ($this->container) {
            $i++;
        }
        $html = $this->content;
        $html = str_replace("\n", ' ', $html);
        $tags = explode('-#-#-#-', str_replace('> <', '>-#-#-#-<', $html));
        // $html = '';
        // foreach ($tags as $tag) {
        //     $html .= $i.wordwrap($tag, 80, "\n".$i)."\n";
        // }
        if (!$this->container) {
            return $html;
        } else {
            $this->container->setContent($html);
            return $this->container->render($indentation, $level, $oneline);
        }
    }

    private function extendStyle(array $style, string $appearance) : array
    {
        foreach ($style as $tag => &$def) {
            if (isset($def['inherit'])) {
                $tmp                         = explode(':', $def['inherit']);
                $inheritedComponentStyle     = $this->builder->getComponentStyle($tmp[0]);
                $variant                     = $tmp[1] ?? null;
                if ($variant) {
                    $variant = $inheritedComponentStyle['variants'][$variant];
                    unset($style['variants']);
                    $inheritedComponentStyle = ArrayHelper::merge($inheritedComponentStyle, $variant);
                }
                $inheritedComponentStyle = StyleAppearanceHelper::apply($inheritedComponentStyle, $appearance);
                unset($inheritedComponentStyle['dark'], $inheritedComponentStyle['markdown'], $inheritedComponentStyle['variants'], $inheritedComponentStyle['inherit']);
                $def = ArrayHelper::merge($inheritedComponentStyle, $def);
            }
        }
        return $style;
    }
}
