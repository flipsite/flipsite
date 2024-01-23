<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractTag extends AbstractComponent
{
    public function build(array $data, array $style, array $options) : void
    {
        if (!isset($data['value']) && !$this->empty && !$this->oneline) {
            $this->render = false;
            return;
        }
        $this->tag = $data['tag'] ?? $this->tag;
        if (isset($data['value'])) {
            $this->setContent($data['value']);
        }
        $this->addStyle($style);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_string($data)) {
            return ['value' => $data];
        }
        if (isset($data['_noContent'])) {
            $this->oneline = true;
        }
        return $data;
    }
}
