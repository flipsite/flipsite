<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Image extends AbstractComponent
{
    use Traits\AssetsTrait;

    protected string $tag  = 'img';
    protected bool $empty  = true;
    protected bool $online = true;

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['src' => $data];
        } elseif (isset($data['value'])) {
            $data['src'] = $data['value'];
            unset($data['value']);
        }
        if (isset($data['external']) && !isset($data['src'])) {
            $data['src'] = $data['external'];
            unset($data['external']);
        }
        return $data;
    }

    public function build(array $data, array $style, array $options): void
    {
        if (isset($data['base64'])) {
            $this->setAttribute('alt', (string)($data['alt'] ?? ''));
            $this->addStyle($style);
            $this->setAttribute('src',$data['base64']);
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
        $this->addStyle($style);

        $imageAttributes = $this->assets->getImageAttributes($src, $style['options'] ?? []);
        if ($imageAttributes) {
            $this->setAttribute('src', $imageAttributes->getSrc());
            $this->setAttribute('srcset', $imageAttributes->getSrcset());
            $this->setAttribute('width', $imageAttributes->getWidth());
            $this->setAttribute('height', $imageAttributes->getHeight());
        }

        // if ($this->isSvg($src)) {
        //     $imageContext = $this->imageHandler->getContext($src, []);
        //     if ($imageContext->getWidth()) {
        //         $this->setAttribute('width', $imageContext->getWidth());
        //     }
        //     if ($imageContext->getHeight()) {
        //         $this->setAttribute('height', $imageContext->getHeight());
        //     }
        //     $this->setAttribute('src', $imageContext->getSrc());
        // } else {
        //     if (($options['webp'] ?? true)) {
        //         $src = str_replace('.jpg', '.webp', $src);
        //         $src = str_replace('.png', '.webp', $src);
        //     }
        //     $imageContext = $this->imageHandler->getContext($src, $options);
        //     $this->setAttribute('src', $imageContext->getSrc());
        //     $this->setAttribute('srcset', $imageContext->getSrcset());
        //     $this->setAttribute('sizes', $sizes);
        //     $this->setAttribute('width', $imageContext->getWidth());
        //     $this->setAttribute('height', $imageContext->getHeight());
        // }



        // if ($isEager) {
        //     $this->builder->dispatch(new Event('preload', 'image', $this));
        // }
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
