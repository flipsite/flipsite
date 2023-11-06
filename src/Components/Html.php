<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Html extends AbstractComponent
{
    use Traits\EnvironmentTrait;

    public function build(array $data, array $style, array $options) : void
    {
        echo 'HTML';
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i   = str_repeat(' ', $indentation * $level);
        return $i.str_replace("\n", "\n".$i, $this->content)."\n";
    }
}
