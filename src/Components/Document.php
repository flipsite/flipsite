<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Document extends AbstractElement
{
    protected string $type = 'html';

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        return "<!DOCTYPE HTML>\n".parent::render($indentation, $level, $oneline);
    }
}
