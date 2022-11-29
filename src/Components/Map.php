<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Map extends AbstractComponent
{
    use Traits\BuilderTrait;

    protected bool $oneline = true;
    protected string $tag   = 'iframe';

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->setAttribute('loading', 'lazy');
        $this->addStyle($style);
        $src = $data['src'] ?? 'https://maps.google.com/maps?q='.$data['name'].','.urlencode($data['address']).'&t=&z=15&ie=UTF8&iwloc=&output=embed';
        $this->setAttribute('data-src-onenter', $src);
        unset($data['flags'], $data['src'], $data['name'], $data['address']);
        foreach ($data as $key => $val) {
            $this->setAttribute($key, $val);
        }
        $this->builder->dispatch(new Event('ready-script', 'iframe-onenter', file_get_contents(__DIR__.'/../../js/ready.iframe-onenter.min.js')));
    }
}
