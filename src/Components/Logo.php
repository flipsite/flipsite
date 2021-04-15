<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Logo extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $type = 'a';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        $external = false;
        $this->setAttribute('href', $this->url($data['url'] ?? 'home', $external));
        unset($data['url']);
        if (isset($data['title'])) {
            $this->setAttribute('title', $data['title']);
            unset($data['title']);
        }

        $fallback = [
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

    protected function normalize($data) : array
    {
        return $data;
    }
}
