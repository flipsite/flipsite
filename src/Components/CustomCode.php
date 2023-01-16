<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class CustomCode extends AbstractElement
{
    private string $html;

    public function __construct(string $html)
    {
        $this->html = $html;
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false): ?string
    {
        $html = '';
        $i    = str_repeat(' ', $indentation * $level);
        $html = $i.str_replace("\n", "\n".$i, $this->html);
        $html .= "\n";
        return $html;
    }
}
