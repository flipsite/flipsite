<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Html extends AbstractComponent
{
    use Traits\EnvironmentTrait;

    public function build(array $data, array $style, string $appearance) : void
    {
        if (mb_strpos($data['value'], '.html')) {
            $filename = $this->environment->getSiteDir().'/'.$data['value'];
            if (file_exists($filename)) {
                $html = file_get_contents($filename);
            }
        } else {
            $html = $data['value'];
        }
        $this->content = $html;
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i   = str_repeat(' ', $indentation * $level);
        return $i.str_replace("\n", "\n".$i, $this->content)."\n";
    }
}
