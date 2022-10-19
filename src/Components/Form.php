<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Form extends AbstractGroup
{
    use Traits\BuilderTrait;

    protected string $tag  = 'form';

    public function build(array $data, array $style, string $appearance): void
    {
        $this->setAttribute('action', $data['action']);
        $this->setAttribute('method', $data['method'] ?? 'post');
        unset($data['action'],$data['method']);
        $children = [];
        foreach ($data['hidden'] ?? [] as $name => $value) {
            $children[] = $this->builder->build('input', [
                'type' => 'hidden',
                'name' => $name,
                'value' => $value,
            ], [], $appearance);
        }
        $this->addChildren($children);
        unset($data['hidden']);
        parent::build($data, $style, $appearance);
    }
}
