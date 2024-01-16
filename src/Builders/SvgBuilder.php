<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Builders\EventListenerInterface;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Builders\Event;

class SvgBuilder implements BuilderInterface, EventListenerInterface
{
    private array $data  = [];

    public function getDocument(Document $document): Document
    {
        if (!count($this->data)) {
            return $document;
        }

        $svg = new Element('svg');
        $svg->setAttribute('version', '1.1');
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $svg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $svg->setAttribute('style', 'display:none;');

        $defs = new Element('defs');
        foreach ($this->data as $id => $data) {
            $g = new Element('g', true);
            $g->setAttribute('id', $id);
            $g->setContent($data);
            $defs->addChild($g);
        }

        $svg->addChild($defs);
        $document->getChild('body')->prependChild($svg);

        return $document;
    }

    public function handleEvent(Event $event) : void
    {
        if ('svg' !== $event->getType()) {
            return;
        }
        $this->data[$event->getId()] = $event->getData();
    }
}
