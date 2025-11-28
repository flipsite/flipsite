<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

class Image extends AbstractComponent
{
    use Traits\AssetsTrait;
    use Traits\BuilderTrait;

    protected string $tag   = 'img';
    protected bool $empty   = true;
    protected bool $oneline = true;

    public function normalize(array $data): array
    {
        if (isset($data['value'])) {
            $data['src'] = $data['value'];
            unset($data['value']);
        } elseif (isset($data['external'])) {
            $data['src'] = $data['external'];
            unset($data['external']);
        }
        if (isset($data['fallback']) && !isset($data['src'])) {
            $data['src'] = $data['fallback'];
            unset($data['fallback']);
        }
        return $data;
    }

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data      = $component->getData();
        $src       = $component->getDataValue('src');
        if (!$src) {
            $this->render = false;
            return;
        }
        $inlineSvg = $component->getDataValue('inlineSvg') ?? false;

        if (!$this->isExternal($src ?? '') && $this->isSvg($src) && $inlineSvg) {
            $this->buildSvg($component, $inherited);
            return;
        }
        $data  = $component->getData();
        $style = $component->getStyle();

        unset($style['strokeWidth'],
            $style['strokeColor'],
            $style['fill'],
            $style['dark:strokeColor'],
            $style['dark:fill']
        );

        if (isset($data['base64'])) {
            $this->setAttribute('alt', (string)($data['alt'] ?? ''));
            $this->setAttribute('src', $data['base64']);
            return;
        }

        $src = $data['src'] ?? false;
        if (!$src) {
            $this->render = false;
            return;
        }

        $isEager = false; // To later preload all eager loading images
        if (!isset($style['options']['loading']) || false !== $style['options']['loading']) {
            $this->setAttribute('loading', $style['options']['loading'] ?? 'lazy');
            $isEager = ($style['options']['loading'] ?? '') === 'eager';
            unset($style['options']['loading']);
        }
        $this->setAttribute('alt', (string)($data['alt'] ?? ''));

        $imageAttributes = $this->assets->getImageAttributes($src, $style['options'] ?? []);
        if ($imageAttributes) {
            $this->setAttribute('src', $imageAttributes->getSrc());
            $this->setAttribute('srcset', $imageAttributes->getSrcset());
            $this->setAttribute('width', $imageAttributes->getWidth());
            $this->setAttribute('height', $imageAttributes->getHeight());
            if ($isEager) {
                $this->builder->dispatch(new Event('preload', 'image', $imageAttributes));
            }
        }
    }

    private function buildSvg(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data          = $component->getData();
        $style         = $component->getStyle();
        unset($style['options']);
        if (!isset($style['fill'])) {
            $style['fill'] = 'fill-current';
        }

        $this->tag     = 'svg';
        $this->empty   = false;
        $this->oneline = true;
        $svg           = $this->assets->getSvg($data['src'] ?? '');
        $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        if ($svg) {
            $this->setMeta('svgHash', $svg->getHash());
            $this->setAttribute('viewBox', $svg->getViewbox());
            $this->setContent('<use xlink:href="#'.$svg->getHash().'"></use>');
            $this->builder->dispatch(new Event('svg', $svg->getHash(), $svg->getDef()));
        } else {
            $this->setAttribute('viewBox', '0 0 100 100');
            $this->setContent('<use xlink:href="#empty"></use>');
            $this->builder->dispatch(new Event('svg', 'empty', '<rect width="100%" height="100%" fill="#eee" />'));
        }
    }

    private function isSvg(string $filename): bool
    {
        return false !== mb_strpos($filename, '.svg');
    }

    private function isExternal(string $src): bool
    {
        return str_starts_with($src, 'http');
    }
}
