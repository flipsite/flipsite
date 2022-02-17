<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Data\Reader;
use Flipsite\Enviroment;

class FaviconBuilder implements BuilderInterface
{
    private Enviroment $enviroment;
    private Reader $reader;
    private ImageHandler $imageHandler;

    public function __construct(Enviroment $enviroment, Reader $reader)
    {
        $this->enviroment   = $enviroment;
        $this->reader       = $reader;
        $this->imageHandler = new ImageHandler(
            $enviroment->getAssetSources(),
            $enviroment->getImgDir(),
            $enviroment->getImgBasePath(),
        );
    }

    public function getDocument(Document $document) : Document
    {
        $favicon = $this->reader->get('favicon');
        if (null === $favicon) {
            $el = new Element('link', true, true);
            $el->setAttribute('rel', 'icon');
            $el->setAttribute('href', 'data:;base64,iVBORw0KGgo=');
            $document->getChild('head')->addChild($el);
            return $document;
        }

        if (isset($favicon['ico'])) {
            $image = $this->imageHandler->getContext($favicon['ico']);
            $el    = new Element('link', true, true);
            $el->setAttribute('rel', 'icon');
            $el->setAttribute('href', $image->getSrc());
            $document->getChild('head')->addChild($el);
        }

        if (isset($favicon['svg'])) {
            $image = $this->imageHandler->getContext($favicon['svg']);
            $el    = new Element('link', true, true);
            $el->setAttribute('rel', 'icon');
            $el->setAttribute('href', $image->getSrc());
            $el->setAttribute('type', 'image/svg+xml');
            $document->getChild('head')->addChild($el);
        }

        if (isset($favicon['apple'])) {
            $image = $this->imageHandler->getContext($favicon['apple'], ['width' => 192, 'height' => 192]);
            $el    = new Element('link', true, true);
            $el->setAttribute('rel', 'apple-touch-icon');
            $el->setAttribute('href', $image->getSrc());
            $document->getChild('head')->addChild($el);
        }

        return $document;
    }
}
