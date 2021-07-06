<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Iframe extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected bool $oneline = true;
    protected string $tag  = 'iframe';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle());
        $this->setAttributes($data->get());
    }

    public function normalize($data) : array
    {
        if (!isset($data['title'])) {
            throw new \Exception('iframe title missing');
        }
        return $data;
    }
}
