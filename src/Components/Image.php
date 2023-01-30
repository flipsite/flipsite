<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Image extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\ImageHandlerTrait;

    protected string $tag  = 'img';
    protected bool $empty  = true;
    protected bool $online = true;

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            return ['src' => $data];
        } elseif (isset($data['value'])) {
            $data['src'] = $data['value'];
            unset($data['value']);
        }
        return $data;
    }

    public function build(array $data, array $style, string $appearance) : void
    {
        $src               = $data['src'];
        $options           = $this->normalizeOptions($style['options'] ?? []);
        $sizes             = $options['sizes'] ?? null;
        unset($options['sizes']);
        $isEager           = false; // To later preload all eager loading images
        if (!isset($options['loading']) || false !== $options['loading']) {
            $this->setAttribute('loading', $options['loading'] ?? 'lazy');
            $isEager = ($options['loading'] ?? '') === 'eager';
            unset($options['loading']);
        }
        $this->setAttribute('alt', (string)($data['alt'] ?? ''));
        $this->addStyle($style);
        if ($this->isSvg($src)) {
            $imageContext = $this->imageHandler->getContext($src, []);
            if ($imageContext->getWidth()) {
                $this->setAttribute('width', $imageContext->getWidth());
            }
            if ($imageContext->getHeight()) {
                $this->setAttribute('height', $imageContext->getHeight());
            }
            $this->setAttribute('src', $imageContext->getSrc());
        } elseif ($this->isExternal($src)) {
            $this->setAttribute('src', $src);
        } else {
            if (($options['webp'] ?? true)) {
                $src = str_replace('.jpg', '.webp', $src);
                $src = str_replace('.png', '.webp', $src);
            }
            $imageContext = $this->imageHandler->getContext($src, $options);
            $this->setAttribute('src', $imageContext->getSrc());
            $this->setAttribute('srcset', $imageContext->getSrcset());
            $this->setAttribute('sizes', $sizes);
            $this->setAttribute('width', $imageContext->getWidth());
            $this->setAttribute('height', $imageContext->getHeight());
        }
        if ($isEager) {
            $this->builder->dispatch(new Event('preload', 'image', $this));
        }
    }

    private function isSvg(string $filename) : bool
    {
        return false !== mb_strpos($filename, '.svg');
    }

    private function isExternal(string $src) : bool
    {
        return str_starts_with($src, 'http');
    }

    private function normalizeOptions(array $options):array
    {
        $screens = [
            'xs'  => 440,
            'sm'  => 640,
            'md'  => 768,
            'lg'  => 1024,
            'xl'  => 1280,
            '2xl' => 1536,
        ];

        if (isset($options['sizes']) && is_array($options['sizes'])) {
            foreach ($options['sizes'] as &$size) {
                $screen = null;
                $size   = explode(':', $size);
                if (count($size) === 2) {
                    $screen = $screens[array_shift($size)];
                }

                $divideBy = 1;
                $size     = explode('/', $size[0]);
                if (count($size) === 2) {
                    $divideBy = intval(array_pop($size));
                }
                $size = $size[0];
                if (isset($screens[$size])) {
                    $size = $screens[$size];
                }
                if ($divideBy !== 1) {
                    $size = intval(floatVal($size) / floatVal($divideBy));
                }
                if (strpos((string)$size, 'vw') === false) {
                    $size .= 'px';
                }
                if (null !== $screen) {
                    $size = '(min-width:'.$screen.'px) '.$size;
                }
            }
            $options['sizes'] = implode(', ', $options['sizes']);
        }
        return $options;
    }
}
