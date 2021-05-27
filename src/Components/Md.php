<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Md extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\EnviromentTrait;
    protected string $type = 'div';

    public function build(array $data, array $style, array $flags) : void
    {
        if (mb_strpos($data['value'], '.md')) {
            $filename = $this->enviroment->getSiteDir().'/'.$data['value'];
            if (file_exists($filename)) {
                $markdown = file_get_contents($filename);
            }
        } else {
            $markdown = $data['value'];
        }
        $this->content = $this->getMarkdown($markdown ?? '', $style ?? null);
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i    = str_repeat(' ', $indentation * $level);
        $html = $this->content;
        $html = str_replace("\n", ' ', $html);
        $tags = explode('-#-#-#-', str_replace('> <', '>-#-#-#-<', $html));
        // $html = '';
        // foreach ($tags as $tag) {
        //     $html .= $i.wordwrap($tag, 80, "\n".$i)."\n";
        // }
        return $html;
    }
}
