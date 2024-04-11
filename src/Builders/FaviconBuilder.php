<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Assets\Assets;
use Flipsite\Data\SiteDataInterface;
use Flipsite\EnvironmentInterface;
use Flipsite\Components\Document;
use Flipsite\Components\Element;

class FaviconBuilder implements BuilderInterface
{
    private Assets $assets;

    public function __construct(private EnvironmentInterface $environment, private SiteDataInterface $siteData)
    {
        $this->assets = new Assets($environment->getAssetSources());
    }

    public function getDocument(Document $document) : Document
    {
        $favicon = $this->siteData->getFavicon();

        if (is_string($favicon)) {
            if (str_ends_with($favicon, '.svg')) {
                $favicon = ['svg' => $favicon];
            } elseif (str_ends_with($favicon, '.png')) {
                $favicon = ['png' => $favicon];
            }
        }

        if (null === $favicon) {
            $el = new Element('link', true, true);
            $el->setAttribute('rel', 'icon');
            $el->setAttribute('href', 'data:;base64,iVBORw0KGgo=');
            $document->getChild('head')->addChild($el);
            return $document;
        }

        // if (isset($favicon['ico'])) {
        //     $image = $this->assets->get($favicon['ico']);
        //     $el    = new Element('link', true, true);
        //     $el->setAttribute('rel', 'icon');
        //     $el->setAttribute('href', $image->getSrc());
        //     $document->getChild('head')->addChild($el);
        // }

        if (isset($favicon['svg'])) {
            $image = $this->assets->getImageAttributes($favicon['svg']);
            $el    = new Element('link', true, true);
            $el->setAttribute('rel', 'icon');
            $el->setAttribute('href', $image->getSrc());
            $el->setAttribute('type', 'image/svg+xml');
            $document->getChild('head')->addChild($el);
        }

        if (isset($favicon['png'])) {
            $image = $this->assets->getImageAttributes($favicon['png'], ['width' => 64, 'height' => 64]);
            $el    = new Element('link', true, true);
            $el->setAttribute('rel', 'icon');
            $el->setAttribute('href', $image->getSrc());
            $el->setAttribute('type', 'image/png');
            $document->getChild('head')->addChild($el);
        }

        if (isset($favicon['apple'])) {
            $image = $this->assets->getImageAttributes($favicon['apple'], ['width' => 192, 'height' => 192]);
            $el    = new Element('link', true, true);
            $el->setAttribute('rel', 'apple-touch-icon');
            $el->setAttribute('href', $image->getSrc());
            $document->getChild('head')->addChild($el);
        }

        return $document;
    }
}
