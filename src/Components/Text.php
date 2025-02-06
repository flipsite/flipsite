<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Text extends AbstractComponent
{
    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data          = $component->getData();
        $this->content = $data['value'] ?? '';
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i    = str_repeat(' ', $indentation * $level);
        $html = $i;
        $html .= $this->content;
        if (!$this->oneline && !$oneline) {
            $html .= "\n";
        }
        return $html;
    }
}
