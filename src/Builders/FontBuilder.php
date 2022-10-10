<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Element;

class FontBuilder implements BuilderInterface
{
    private array $links;
    private ?string $bodyWeight = null;

    public function __construct(array $fonts)
    {
        $googleUrl = $this->getGoogle($fonts);
        if (null !== $googleUrl) {
            $link = new Element('link', true, true);
            $link->setAttributes([
                'rel'  => 'preconnect',
                'href' => 'https://fonts.gstatic.com',
            ]);
            $this->links[] = $link;

            $link = new Element('link', true, true);
            $link->setAttributes([
                'rel'  => 'preload',
                'as'   => 'style',
                'href' => $googleUrl,
            ]);
            $this->links[] = $link;

            $link = new Element('link', true, true);
            $link->setAttributes([
                'rel'    => 'stylesheet',
                'href'   => $googleUrl,
                'media'  => 'print',
                'onload' => "this.media='all'",
            ]);
            $this->links[] = $link;

            if (isset($fonts['sans']['body'])) {
                $this->bodyWeight = $fonts['sans']['body'];
            }
        }
    }

    public function getDocument(Document $document) : Document
    {
        foreach ($this->links as $link) {
            $document->getChild('head')->addChild($link);
        }
        if ($this->bodyWeight) {
            $document->getChild('body')->addStyle(['fontWeight' => 'font-'.$this->bodyWeight]);
        }
        return $document;
    }

    public function getGoogle(array $fonts) : ?string
    {
        $fonts = array_filter($fonts, function ($value, $key) {
            return is_array($value) && 'google' === trim(mb_strtolower($value['provider']));
        }, ARRAY_FILTER_USE_BOTH);
        if (!$fonts) {
            return null;
        }

        $families = [];
        $all      = [];
        foreach ($fonts as $font) {
            $font  = $this->normalizeGoogleFont($font);
            $param = $font['family'];
            if (count($font['italic'])) {
                foreach ($font['normal'] as $w) {
                    $all[] = '0,'.$w;
                }
                foreach ($font['italic'] as $w) {
                    $all[] = '1,'.$w;
                }
                $param .= ':ital,wght@'.implode(';', $all);
            } elseif (count($font['normal']) && $font['normal'] != [400]) {
                $param .= ':wght@'.implode(';', $font['normal']);
            }
            $families[] = $param;
        }
        return 'https://fonts.googleapis.com/css2?family='.implode('&family=', $families).'&display=swap';
    }

    private function normalizeGoogleFont(array $font) : array
    {
        $font['family'] = urlencode(trim($font['family']));
        $font['normal'] = $this->toIntArray($font['normal'] ?? []);
        $font['italic'] = $this->toIntArray($font['italic'] ?? $font['italics'] ?? []);
        return $font;
    }

    private function toIntArray($data) : array
    {
        if (is_int($data)) {
            return [$data];
        }
        if (is_string($data)) {
            $data = explode(',', str_replace(' ', '', $data));
        }
        array_walk($data, 'intval');
        return $data;
    }
}
