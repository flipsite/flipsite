<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Iframe extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'iframe';

    public function build(array $data, array $style, array $options) : void
    {
        $this->setAttribute('loading', 'lazy');
        $this->setAttribute('data-load-onenter', 'lazy');
        $this->addStyle($style);
        unset($data['flags']);
        foreach ($data as $key => $val) {
            $this->setAttribute($key, $val);
        }
        if (isset($data['src'])) {
            $this->setAttribute('data-src', $data['src']);
        }
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (!isset($data['title'])) {
            throw new \Exception('iframe title missing');
        }
        return $data;
    }
}
