<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Li extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected string $type = 'li';

    public function build(array $data, array $style, array $flags) : void
    {
        if (null !== $style) {
            $this->addStyle($style);
        }
        $fallback = [
            'text' => 'plain',
            'icon' => 'svg',
        ];
        foreach ($data as $key => $val) {
            $type      = $style[$key]['type'] ?? $fallback[$key] ?? $key;
            $component = $this->builder->build($type, $val, $style[$key] ?? []);
            if (null !== $component) {
                $this->addChild($component);
            }
        }
    }
}
