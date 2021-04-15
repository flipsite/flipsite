<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Pre extends AbstractElement
{
    protected string $type = 'pre';

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        return str_repeat(' ', $indentation * $level).'<pre'.$this->renderAttributes().'>'.$this->content.'</pre>'."\n";
    }
}
