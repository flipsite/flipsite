<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class IframeVideo extends AbstractGroup
{
    use Traits\BuilderTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style,array $options) : void
    {
        $this->setAttribute('onclick', 'playIframeVideo(this)');

        $title = $data['title'];
        unset($data['title']);

        if (isset($data['youtube'])) {
            $data['iframe'] = [
                'title' => $title,
                '_attr' => [
                    'loading'         => 'lazy',
                    'frameborder'     => '0',
                    'allow'           => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
                    'allowfullscreen' => true,
                    'data-src'        => '//www.youtube.com/embed/'.$data['youtube'].'?autoplay=1&mute=1',
                ]
            ];
        } elseif (isset($data['vimeo'])) {
            $data['iframe'] = [
                'title' => $title,
                '_attr' => [
                    'loading'         => 'lazy',
                    'frameborder'     => '0',
                    'allow'           => 'autoplay; fullscreen',
                    'allowfullscreen' => true,
                    'data-src'        => '//player.vimeo.com/video/'.$data['vimeo'].'?autoplay=1&muted=1'
                ]
            ];
        }

        parent::build($data, $style, $options);
        $this->builder->dispatch(new Event('global-script', 'youtube', file_get_contents(__DIR__.'/../../js/play-iframe-video.min.js')));
    }
}
