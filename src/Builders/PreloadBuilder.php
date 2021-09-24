<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Components\Event;
use Flipsite\Components\ComponentListenerInterface;

class PreloadBuilder implements BuilderInterface, ComponentListenerInterface
{
    private array $links = [];

    public function getDocument(Document $document) : Document
    {
        foreach ($this->links as $link) {
            $document->getChild('head')->addChild($link);
        }
        return $document;
    }

    public function handleComponentEvent(Event $event) : void
    {
        switch ($event->getType()) {
            case 'preload':
                if ($event->getId() === 'image') {
                    $img = $event->getData();
                    $link = new Element('link', true, true);
                    $link->setAttribute('rel', 'preload');
                    $link->setAttribute('href', $img->getAttribute('src'));
                    $link->setAttribute('as', 'image');
                    $link->setAttribute('imagesrcset', $img->getAttribute('srcset'));
                    $link->setAttribute('imagesizes', $img->getAttribute('sizes'));
                    $this->links[] = $link;
                }
                break;
        }
    }
}
