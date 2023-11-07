<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Script extends AbstractComponent
{
    protected string $tag   = 'script';
    protected bool $oneline = true;

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        if (isset($data['value'])) {
            unset($data['_attr']['src']);
            unset($data['_attr']['defer']);
        }
        return $data;
    }

    public function build(array $data, array $style, array $options): void
    {
        if (isset($data['value'])) {
            $this->setContent($data['value']);
        }
    }
}
