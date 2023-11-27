<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Youtube extends AbstractGroup
{
    use Traits\BuilderTrait;

    protected bool $oneline = true;
    protected string $tag   = 'iframe';

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['value' => $data];
        }
        return $data;
    }

    public function build(array $data, array $style, array $options): void
    {
        if (isset($data['base64bg'])) {
            $this->setAttribute('style', 'background: url('.$data['base64bg'].') 0% 0% / cover no-repeat;');
        }
        $this->setAttribute('loading', 'lazy');
        $this->setAttribute('frameborder', '0');
        $this->setAttribute('allowfullscreen', true);
        $this->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');

        $src = $data['privacy'] ?? false ?
            'https://www.youtube-nocookie.com/embed/' :
            'https://www.youtube.com/embed/';
        $src .= $data['value'] ?? '';

        $query = [];
        if (!($data['controls'] ?? false)) {
            $query['controls'] = 0;
        }
        if ($data['start'] ?? false) {
            $query['start'] = intval($data['start']);
        }

        if (count($query)) {
            $src .= '?'.http_build_query($query);
        }
        $this->setAttribute('src', $src);
        $this->addStyle($style);


        // if (isset($data['value'])) {
        //     $sources = $this->videoHandler->getSources($data['value']);
        //     foreach ($sources as $type => $src) {
        //         $s = new Element('source', true);
        //         $s->setAttribute('src', $src);
        //         $s->setAttribute('type', $type);
        //         $this->addChild($s);
        //     }
        // }
    }

    // public function build(array $data, array $style,array $options) : void
    // {
    //     $this->setAttribute('onclick', 'playIframeVideo(this)');

    //     $title = $data['title'];
    //     unset($data['title']);

    //     if (isset($data['youtube'])) {
    //         $data['iframe'] = [
    //             'title' => $title,
    //             '_attr' => [
    //                 'loading'         => 'lazy',
    //                 'frameborder'     => '0',
    //                 'allow'           => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
    //                 'allowfullscreen' => true,
    //                 'data-src'        => '//www.youtube.com/embed/'.$data['youtube'].'?autoplay=1&mute=1',
    //             ]
    //         ];
    //     } elseif (isset($data['vimeo'])) {
    //         $data['iframe'] = [
    //             'title' => $title,
    //             '_attr' => [
    //                 'loading'         => 'lazy',
    //                 'frameborder'     => '0',
    //                 'allow'           => 'autoplay; fullscreen',
    //                 'allowfullscreen' => true,
    //                 'data-src'        => '//player.vimeo.com/video/'.$data['vimeo'].'?autoplay=1&muted=1'
    //             ]
    //         ];
    //     }

    //     parent::build($data, $style, $options);
    //     $this->builder->dispatch(new Event('global-script', 'youtube', file_get_contents(__DIR__.'/../../js/play-iframe-video.min.js')));
    // }
}
