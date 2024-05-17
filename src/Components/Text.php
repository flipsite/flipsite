<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Text extends AbstractComponent
{
    protected string $plainText = '';

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            $data = ['value' => (string)$data];
        }
        return $data;
    }

    public function build(array $data, array $style, array $options) : void
    {
        $this->plainText = $data['value'] ?? '';
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $i    = str_repeat(' ', $indentation * $level);
        $html = $i;
        $html .= $this->plainText;
        if (!$this->oneline && !$oneline) {
            $html .= "\n";
        }
        return $html;
    }
}
