<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Element;

class PreloadBuilder implements BuilderInterface, EventListenerInterface
{
    private array $links = [];

    public function getDocument(Document $document) : Document
    {
        foreach ($this->links as $link) {
            $document->getChild('head')->addChild($link);
        }
        return $document;
    }

    public function handleEvent(Event $event) : void
    {
        switch ($event->getType()) {
            case 'preload':
                if ($event->getId() === 'image') {
                    $imageAttributes  = $event->getData();
                    $link             = new Element('link', true, true);
                    $link->setAttribute('rel', 'preload');
                    $link->setAttribute('as', 'image');
                    $link->setAttribute('href', $imageAttributes->getSrc());
                    $link->setAttribute('imagesrcset', $imageAttributes->getSrcset());
                    $this->links[] = $link;
                } elseif ($event->getId() === 'background') {
                    $imageAttributes  = $event->getData();
                    $link             = new Element('link', true, true);
                    $link->setAttribute('rel', 'preload');
                    $link->setAttribute('as', 'image');
                    $link->setAttribute('href', $imageAttributes->getSrc());
                    $link->setAttribute('imagesrcset', $imageAttributes->getSrcset());
                    $this->links[] = $link;
                } elseif ($event->getId() === 'custom') {
                    $link             = new Element('link', true, true);
                    $link->setAttribute('rel', 'preload');
                    foreach ($event->getData() as $attribute => $value) {
                        $link->setAttribute($attribute, $value);
                    }
                    $this->links[] = $link;
                }
                break;
        }
    }
}
