<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class A extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $tag = 'a';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle());
        $external = false;
        $onclick = $data->get('onclick', true);
        if ($onclick) {
            $this->setAttribute('onclick', $onclick);
        }
        $this->setAttribute('href', $this->url($data->get('url'), $external));
        if ($external) {
            $this->setAttribute('target', '_blank');
            $this->setAttribute('rel', 'noopener noreferrer');
        }
        $data->unset('url');
        $components = $this->builder->build($data->get(), $data->getStyle(), $data->getAppearance());
        $this->addChildren($components);
    }
}
