<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use Flipsite\Data\YamlComponentData;

final class Youtube extends AbstractGroup
{
    use Traits\BuilderTrait;

    protected bool $oneline = true;
    protected string $tag   = 'iframe';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $style = $component->getStyle();
        $width = $height = null;
        if (isset($data['dimensions'])) {
            $parts  = explode('x', $data['dimensions']);
            $width  = $parts[0] ?? null;
            $height = $parts[1] ?? null;
        }
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
        $iframe->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        $iframe->setAttribute('title', $title);
        if ($width && $height && !isset($style['aspectRatio'])) {
            $style['aspectRatio'] = 'aspect-'.$width.'/'.$height;
        }

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
            strpos($src, '?') ? $src .= '&' : $src .= '?';
            $src .= http_build_query($query);
        }
        if ('onclick' === ($data['loading'] ?? 'onclick')) {
            $iframe->setAttribute('data-youtube-play', $src);
            $iframe->setAttribute('style', 'pointer-events:none');
            $this->addChild($iframe);
            if (isset($style['aspectRatio'])) {
                $this->addStyle(['aspectRatio' => $style['aspectRatio']]);
            }
            $iconComponentData = new YamlComponentData($component->getPath(), $component->getId().'.icon', 'icon', ['value' => $data['playIcon']['value'] ?? 'simpleicons/youtube'], $style['playIcon'] ?? []);
            $icon              = $this->builder->build($iconComponentData, $inherited);
            $this->builder->dispatch(new Event('ready-script', 'youtube-play', file_get_contents(__DIR__.'/../../js/dist/youtube-play.min.js')));
            $this->addChild($icon);
        } else {
            $iframe->setAttribute('data-lazyiframe', $src);
            $this->builder->dispatch(new Event('ready-script', 'lazyiframe', file_get_contents(__DIR__.'/../../js/dist/lazyiframe.min.js')));
        }
        $iframe->addStyle($style);
    }
}
