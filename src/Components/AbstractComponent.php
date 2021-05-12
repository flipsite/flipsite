<?php

declare(strict_types=1);

namespace Flipsite\Components;

abstract class AbstractComponent extends AbstractElement
{
    public function with($data, ?array $style = null, array $flags, string $appearance = 'light') : void
    {
        $style = $style ?? [];
        $this->build($this->normalize($data), $style, $flags, $appearance);
    }

    abstract public function build(array $data, array $style, array $flags) : void;

    protected function normalize($data) : array
    {
        if (!is_array($data)) {
            return ['value' => $data];
        }
        if (isset($data['tag'])) {
            $this->type = $data['tag'];
            unset($data['tag']);
        }
        return $data;
    }
}
