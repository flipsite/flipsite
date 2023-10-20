<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\StyleAppearanceHelper;

final class Richtext extends AbstractComponent
{
    use Traits\EnvironmentTrait;
    use Traits\BuilderTrait;
    protected string $tag   = 'div';

    public function build(array $data, array $style, array $options) : void
    {
        
        $lexer = new \nadar\quill\Lexer($data['value']);

        // $heading = new \nadar\quill\listener\Heading();
        // $heading->wrapper = '<h{heading} class="text-red">{__buffer__}</h{heading}>';
        // $lexer->registerListener($heading);

        $this->content = trim($lexer->render());

        $this->content = str_replace('<h1', '<h1 class="text-red-500 text-40"', $this->content);
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i    = str_repeat(' ', $indentation * $level);
        $ii   = str_repeat(' ', $indentation * ($level + 1));
        $html = '';
        
            $container = new Element($this->containerStyle['tag'] ?? 'div');
            unset($this->containerStyle['tag']);
            $container->addStyle($this->containerStyle);
            $rows = explode("\n", $container->render(2, 0));
            $html .= $i.$rows[0]."\n";
            foreach (explode("\n", $this->content) as $row) {
                $html .= $ii.$row."\n";
            }
            $html .= $i.$rows[2]."\n";
        
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

    // private function extendStyle(array $style, string $appearance) : array
    // {
    //     foreach ($style as $tag => &$def) {
    //         // if (isset($def['inherit'])) {
    //         //     $tmp                         = explode(':', $def['inherit']);
    //         //     $inheritedComponentStyle     = $this->builder->getComponentStyle($tmp[0]);
    //         //     $variant                     = $tmp[1] ?? null;
    //         //     if ($variant) {
    //         //         $variant = $inheritedComponentStyle['variants'][$variant];
    //         //         unset($style['variants']);
    //         //         $inheritedComponentStyle = ArrayHelper::merge($inheritedComponentStyle, $variant);
    //         //     }
    //         //     $inheritedComponentStyle = StyleAppearanceHelper::apply($inheritedComponentStyle, $appearance);
    //         //     unset($inheritedComponentStyle['dark'], $inheritedComponentStyle['markdown'], $inheritedComponentStyle['variants'], $inheritedComponentStyle['inherit']);
    //         //     $def = ArrayHelper::merge($inheritedComponentStyle, $def);
    //         // }
    //     }
    //     return $style;
    // }
}
