<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Iframe extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected bool $oneline = true;
    protected string $type  = 'iframe';

    public function build(array $data, array $style) : void
    {
        $this->addStyle($style);
        $this->setAttributes($data);
    }

    public function normalize($data) : array
    {
        if (!isset($data['title'])) {
            throw new \Exception('iframe title missing');
        }
        return $data;
    }
}
