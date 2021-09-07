<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Video extends AbstractComponent
{
    use Traits\BuilderTrait;

    protected string $tag = 'div';

    public function with(ComponentData $data) : void
    {
        if ('youtube' === $data->get('type', true)) {
            $this->youtube($data);
        }
    }

    private function youtube(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $this->setAttribute('onclick', 'youtube(this)');

        $play = $this->builder->build(['svg'=>$data->get('play')], ['svg'=>$data->getStyle('play')], $data->getAppearance());
        $this->addChildren($play);

        $data->get('vid');
        $iframe = new Element('iframe', true);
        $iframe->addStyle($data->getStyle('iframe'));
        $iframe->setAttributes([
            'loading'         => 'lazy',
            'frameborder'     => '0',
            'allow'           => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
            'allowfullscreen' => true,
            'data-src'        => '//www.youtube.com/embed/'.$data->get('vid').'?autoplay=1&mute=1',
            'title'           => $data->get('title'),
        ]);
        $this->addChild($iframe);

        $posterImage = $data->get('poster', true);
        if ($posterImage) {
            $poster = $this->builder->build(['image'=>$posterImage], ['image'=>$data->getStyle('poster')], $data->getAppearance());
            $this->addChildren($poster);
        }


        $this->builder->dispatch(new Event('global-script', 'youtube', file_get_contents(__DIR__.'/youtube.js')));
    }
}
