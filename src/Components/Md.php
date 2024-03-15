<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Md extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\BuilderTrait;
    protected string $type = 'div';

    private ?array $containerStyle = null;

    public function build(array $data, array $style, array $options) : void
    {
        if (mb_strpos($data['value'], '.md')) {
            $filename = $this->environment->getSiteDir().'/'.$data['value'];
            if (file_exists($filename)) {
                $markdown = file_get_contents($filename);
            }
        } else {
            $markdown = $data['value'];
        }

        $this->containerStyle = $style ?? null;

        $this->content = $this->getMarkdown($markdown ?? '', $style ?? null, $options['appearance']);
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i    = str_repeat(' ', $indentation * $level);
        $ii   = str_repeat(' ', $indentation * ($level + 1));
        $html = '';
        if (null !== $this->containerStyle) {
            $container = new Element($this->containerStyle['tag'] ?? 'div');
            unset($this->containerStyle['tag']);
            $container->addStyle($this->containerStyle);
            $rows = explode("\n", $container->render(2, 0));
            $html .= $i.$rows[0]."\n";
            foreach (explode("\n", $this->content) as $row) {
                $html .= $ii.$row."\n";
            }
            $html .= $i.$rows[2]."\n";
        } else {
            foreach (explode("\n", $this->content) as $row) {
                $html .= $i.$row."\n";
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
}
