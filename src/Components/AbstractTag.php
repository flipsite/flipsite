<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractTag extends AbstractComponent
{
    public function build(array $data, array $style, array $options) : void
    {
        if (!$data['value'] && !$this->empty) {
            $this->render = false;
            return;
        }
        $this->tag = $data['tag'] ?? $this->tag;
        $this->setContent($data['value']);
        $this->addStyle($style);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_string($data)) {
            return ['value' => $data];
        }
        return $data;
    }
}
