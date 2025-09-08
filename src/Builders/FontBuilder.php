<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Utils\GoogleFonts;

class FontBuilder implements BuilderInterface
{
    private array $links        = [];
    private ?string $bodyWeight = null;
    private string $style       = '';

    public function __construct(array $fonts)
    {
        if (isset($fonts['sans']['body'])) {
            $this->bodyWeight = $fonts['sans']['body'];
        }
        $googleFonts = new GoogleFonts($fonts);
        $googleUrl   = $googleFonts->getUrl();
        if (null !== $googleUrl) {
            $link = new Element('link', true, true);
            $link->setAttributes([
                'rel'  => 'preconnect',
                'href' => 'https://fonts.googleapis.com',
            ]);
            $this->links[] = $link;
            $link          = new Element('link', true, true);
            $link->setAttributes([
                'rel'         => 'preconnect',
                'href'        => 'https://fonts.gstatic.com',
                'crossorigin' => true
            ]);
            $this->links[] = $link;

            $link = new Element('link', true, true);
            $link->setAttributes([
                'rel'         => 'preload',
                'as'          => 'style',
                'href'        => $googleUrl
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
        }
        $this->style = $this->getLocal($fonts);
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function getDocument(Document $document): Document
    {
        foreach ($this->links as $link) {
            $document->getChild('head')->addChild($link);
        }
        if ($this->style) {
            $style = new Element('style', true);
            $style->setContent($this->style);
            $document->getChild('head')->addChild($style);
        }
        if ($this->bodyWeight) {
            $document->getChild('body')->addStyle(['fontWeight' => 'font-'.$this->bodyWeight]);
        }
        return $document;
    }

    private function getLocal(array $fonts): string
    {
        $fonts = array_filter($fonts, function ($value, $key) {
            return is_array($value) && 'local' === trim(mb_strtolower($value['provider'] ?? ''));
        }, ARRAY_FILTER_USE_BOTH);
        if (!$fonts) {
            return '';
        }
        $style = '';
        foreach ($fonts as $type => $font) {
            foreach ($font['files'] as $file) {
                $style .= '@font-face{';
                $style .= 'font-family:'.$font['family'].';';
                $style .= 'font-style:'.$file['style'].';';
                $style .= 'font-weight:'.$file['weight'].';';
                $style .= 'font-display:'.$file['display'].';';
                $style .= 'src:'.$file['src'].';';
                $style .= 'unicode-range:'.str_replace(' ', '', $file['unicode-range']).'}';
            }
        }
        return $style;
    }
}
