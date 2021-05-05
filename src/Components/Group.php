<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Group extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $type = 'div';

    public function build(array $data, array $style, array $flags) : void
    {
        if (isset($data['id'])) {
            $this->setAttribute('id', $data['id']);
            unset($data['id']);
        }
        $this->addStyle($style['container']);
        foreach ($data as $key => $val) {
            $type      = $style[$key]['type'] ?? $key;
            $component = $this->builder->build($type, $val, $style[$key] ?? []);
            if (null !== $component) {
                $this->addChild($component);
            }
        }
    }
}
