<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\InlineScript;

class SchemaOrgBuilder implements BuilderInterface, EventListenerInterface
{
    private array $graph = [];

    public function getDocument(Document $document) : Document
    {
        if (0 === count($this->graph)) {
            return $document;
        }

        $json = ['@context' => 'https://schema.org', '@graph' => []];
        foreach ($this->graph as $item) {
            if (is_array($item)) {
                $json['@graph'][] = $item;
            }
        }

        $script = new InlineScript();
        $script->setAttribute('type', 'application/ld+json');
        $script->addCode(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        $document->getChild('body')->addChild($script);
        return $document;
    }

    public function handleEvent(Event $event) : void
    {
        switch ($event->getType()) {
            case 'schemaorg.graph':
                $this->graph[$event->getId()] = $event->getData();
                return;
        }
    }
}
