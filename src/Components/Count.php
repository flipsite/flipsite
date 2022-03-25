<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Count extends AbstractComponent
{
    protected string $tag = 'div';

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_string($data)) {
            $data = ['value' => $data];
        }
        if (is_array($data)) {
            $data = ['value' => count($data)];
        }
        return $data;
    }

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->addStyle($style);
        $this->setContent((string)$data['value']);
    }
}
