<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class A extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $type = 'a';

    public function build(array $data, array $style, array $flags, string $appearance = 'light') : void
    {
        if (isset($data['id'])) {
            $this->setAttribute('id', $data['id']);
            unset($data['id']);
        }
        $this->addStyle($style);

        $external = false;
        $this->setAttribute('href', $this->url($data['url'], $external));
        if ($external) {
            $this->setAttribute('target', '_blank');
            $this->setAttribute('rel', 'noopener noreferrer');
        }
        unset($data['url']);
        $fallback = [
            'text' => 'plain',
            'icon' => 'svg',
        ];
        foreach ($data as $key => $val) {
            $type      = $style[$key]['type'] ?? $fallback[$key] ?? $key;
            $component = $this->builder->build($type, $val, $style[$key] ?? [], $appearance);
            if (null !== $component) {
                $this->addChild($component);
            }
        }
    }
}
