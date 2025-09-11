<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Element;

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
        $svg->addCss('display', 'none');

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

    public function getData(string $id): ?string
    {
        return $this->data[$id] ?? null;
    }

    public function handleEvent(Event $event): void
    {
        if ('svg' !== $event->getType()) {
            return;
        }
        $this->data[$event->getId()] = $event->getData();
    }
}
