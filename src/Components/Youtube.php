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
    use Traits\AspectRatioTrait;

    protected string $tag   = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $style = $component->getStyle();
        $width = $height = null;
        if (isset($data['dimensions'])) {
            $parts  = explode('x', $data['dimensions']);
            $width  = $parts[0] ?? 100;
            $height = $parts[1] ?? 100;
        }
        $title = $this->getAttribute('title') ?? 'Youtube Video';
        $this->setAttribute('title', null);
        $aspectRatioValues          = $this->simplifyAspectRatio(intval($width), intval($height));
        $aspectRatio                = 'aspect-' . ($aspectRatioValues[0]).'/'.($aspectRatioValues[1]);
        $style['aspectRatio']       = $aspectRatio;
        $this->addStyle($style);
        $iframe = new Element('iframe', true);
        if (isset($data['base64bg'])) {
            $ifame->addCss('background', 'url('.$data['base64bg'].') 0% 0% / cover no-repeat;');
        }
        $iframe->setAttribute('loading', 'lazy');
        $iframe->setAttribute('frameborder', '0');
        $iframe->setAttribute('allowfullscreen', true);
        $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        $iframe->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        $iframe->setAttribute('title', $title);
        $iframe->addStyle([
            'width'         => 'w-full',
            'height'        => 'h-full',
            'borderRadius'  => $style['borderRadius'] ?? null,
        ]);

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
            $this->setAttribute('data-youtube-play', $src);
            $iframe->addCss('pointer-events', 'none');
            $iconComponentData = new YamlComponentData($component->getPath(), $component->getId().'.icon', 'icon', ['value' => $data['playIcon']['value'] ?? 'simpleicons/youtube'], $style['playIcon'] ?? []);
            $icon              = $this->builder->build($iconComponentData, $inherited);
            $this->builder->dispatch(new Event('ready-script', 'youtube-play', file_get_contents(__DIR__.'/../../js/dist/youtube-play.min.js')));
            $this->addChild($icon);

            if (isset($data['poster'])) {
                $posterStyle                  = $style['poster'] ?? [];
                $posterStyle['position']      = 'absolute';
                $posterStyle['inset']         = 'inset-0';
                $posterStyle['width']         = 'w-full';
                $posterStyle['height']        = 'h-full';
                $posterStyle['objectFit']     = 'object-cover';
                $posterStyle['borderRadius']  = $style['borderRadius'] ?? null;
                $posterStyle['pointerEvents'] = 'pointer-events-none';
                $posterComponentData          = new YamlComponentData($component->getPath(), $component->getId().'.poster', 'image', $data['poster'], $posterStyle);
                $poster                       = $this->builder->build($posterComponentData, $inherited);
                $this->addChild($poster);
            }
        } else {
            $iframe->setAttribute('data-lazyiframe', $src);
            $this->builder->dispatch(new Event('ready-script', 'lazyiframe', file_get_contents(__DIR__.'/../../js/dist/lazyiframe.min.js')));
        }
        $this->addChild($iframe);
    }
}
