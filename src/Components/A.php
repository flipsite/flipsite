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
        $this->addStyle($style);
        $external = false;
        $this->setAttribute('href', $this->url($data['url'], $external));
        if ($external) {
            $this->setAttribute('target', '_blank');
            $this->setAttribute('rel', 'noopener noreferrer');
        }
        unset($data['url']);
        $components = $this->builder->buildGroup($data, $style, $appearance);
        $this->addChildren($components);
    }
}
