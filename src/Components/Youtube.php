<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Youtube extends AbstractComponent
{
    use Traits\BuilderTrait;

    protected string $type = 'div';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        $this->setAttribute('onclick', 'youtube(this)');

        $icon = $this->builder->build('svg', $data['icon'], $style['icon']);
        $this->addChild($icon);

        $iframe = new Element('iframe', true);
        $iframe->addStyle($style['iframe'] ?? []);
        $iframe->setAttributes([
            'loading'         => 'lazy',
            'frameborder'     => '0',
            'allow'           => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
            'allowfullscreen' => true,
            'data-src'        => '//www.youtube.com/embed/'.$data['vid'].'?autoplay=1&mute=1',
            'title'           => $data['title'],
        ]);
        $this->addChild($iframe);

        $this->builder->dispatch(new Event('global-script', 'youtube', file_get_contents(__DIR__.'/youtube.js')));
    }

    protected function normalize($data) : array
    {
        if (!isset($data['vid'])) {
            throw new \Exception('YouTube ID missing');
        }
        if (!isset($data['title'])) {
            throw new \Exception('iframe title missing');
        }
        return $data;
    }
}
