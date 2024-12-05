<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;

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
        $title = $this->getAttribute('title') ?? 'Youtube Video';
        $this->setAttribute('title', null);
        $iframe = $this;
        if ('onclick' === ($data['loading'] ?? 'onclick')) {
            $this->tag      = 'div';
            $this->oneline  = false;
            $iframe         = new Element('iframe', true);
        }
        if (isset($data['base64bg'])) {
            $ifame->setAttribute('style', 'background: url('.$data['base64bg'].') 0% 0% / cover no-repeat;');
        }
        $iframe->setAttribute('loading', 'lazy');
        $iframe->setAttribute('frameborder', '0');
        $iframe->setAttribute('allowfullscreen', true);
        $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        $iframe->setAttribute('title', $title);

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

        if ($data['muted'] ?? false) {
            $query['mute'] = intval($data['muted']);
        }
        if ($data['autoplay'] ?? false) {
            $query['autoplay'] = intval($data['autoplay']);
        }

        if (count($query)) {
            $src .= '?'.http_build_query($query);
        }
        if ('onclick' === ($data['loading'] ?? 'onclick')) {
            $iframe->setAttribute('data-youtube-play', $src);
            $iframe->setAttribute('style', 'pointer-events:none');
            $this->addChild($iframe);
            $icon = $this->builder->build('svg', ['value' => $data['playIcon']], $style['playIcon'] ?? [], $options);
            $this->setAttribute('data-youtube-play', true);
            $this->builder->dispatch(new Event('ready-script', 'youtube-play', file_get_contents(__DIR__.'/../../js/dist/youtube-play.min.js')));
            $this->addChild($icon);
            $this->addStyle($style);
        } else {
            $iframe->setAttribute('data-lazyiframe', $src);
            $this->builder->dispatch(new Event('ready-script', 'lazyiframe', file_get_contents(__DIR__.'/../../js/dist/lazyiframe.min.js')));
        }
        $iframe->addStyle($style);
    }
}
