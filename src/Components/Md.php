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

    private ?array $containerStyle = null;

    public function build(array $data, array $style, string $appearance) : void
    {
        if (mb_strpos($data['value'], '.md')) {
            $filename = $this->enviroment->getSiteDir().'/'.$data['value'];
            if (file_exists($filename)) {
                $markdown = file_get_contents($filename);
            }
        } else {
            $markdown = $data['value'];
        }

        $this->containerStyle = $style['container'] ?? null;


        unset($style['container']);

        $this->content = $this->getMarkdown($markdown ?? '', $style ?? null, $appearance);
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i = str_repeat(' ', $indentation * $level);
        $ii = str_repeat(' ', $indentation * ($level+1));
        $html = '';
        if (null !== $this->containerStyle) {
            $container = new Element($this->containerStyle['tag']??'div');
            unset($this->containerStyle['tag']);
            $container->addStyle($this->containerStyle);
            $rows = explode("\n", $container->render(2, 0));
            $html.= $i.$rows[0]."\n";
            foreach (explode("\n", $this->content) as $row) {
                $html.= $ii.$row."\n";
            }
            $html.= $i.$rows[2]."\n";
        } else {
            foreach (explode("\n", $this->content) as $row) {
                $html.= $i.$row."\n";
            }
        }
        return $html;







        // $i    = str_repeat(' ', $indentation * $level);
        // if ($this->container) {
        //     $i++;
        // }
        // $html = $this->content;
        // print_r($this->content);
        // //$html = str_replace("\n", ' ', $html);
        // // $tags = explode('-#-#-#-', str_replace('> <', '>-#-#-#-<', $html));
        // // $html = '';
        // // foreach ($tags as $tag) {
        // //     $html .= $i.wordwrap($tag, 80, "\n".$i)."\n";
        // // }
        // if (!$this->container) {
        //     echo "Hsdfdfs";
        //     return $html;
        // } else {
        //     $this->container->setContent($html);
        //     return $this->container->render($indentation, $level, $oneline);
        // }
    }

    private function extendStyle(array $style, string $appearance) : array
    {
        foreach ($style as $tag => &$def) {
            // if (isset($def['inherit'])) {
            //     $tmp                         = explode(':', $def['inherit']);
            //     $inheritedComponentStyle     = $this->builder->getComponentStyle($tmp[0]);
            //     $variant                     = $tmp[1] ?? null;
            //     if ($variant) {
            //         $variant = $inheritedComponentStyle['variants'][$variant];
            //         unset($style['variants']);
            //         $inheritedComponentStyle = ArrayHelper::merge($inheritedComponentStyle, $variant);
            //     }
            //     $inheritedComponentStyle = StyleAppearanceHelper::apply($inheritedComponentStyle, $appearance);
            //     unset($inheritedComponentStyle['dark'], $inheritedComponentStyle['markdown'], $inheritedComponentStyle['variants'], $inheritedComponentStyle['inherit']);
            //     $def = ArrayHelper::merge($inheritedComponentStyle, $def);
            // }
        }
        return $style;
    }
}
