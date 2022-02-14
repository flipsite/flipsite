<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Youtube extends AbstractGroup
{
    use Traits\BuilderTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->setAttribute('onclick', 'youtube(this)');

        $title = $data['title'];
        unset($data['title']);

        $id = $data['id'];
        unset($data['id']);

        $data['iframe'] = [
            'title' => $title,
            '_attr' => [
                'loading'         => 'lazy',
                'frameborder'     => '0',
                'allow'           => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
                'allowfullscreen' => true,
                'data-src'        => '//www.youtube.com/embed/'.$id.'?autoplay=1&mute=1',
            ]
        ];

        parent::build($data, $style, $appearance);
        $this->builder->dispatch(new Event('global-script', 'youtube', file_get_contents(__DIR__.'/../../js/youtube.min.js')));
    }
}
