<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Document;
use Flipsite\Components\Event;
use Flipsite\Components\Script;

class ScriptBuilder implements BuilderInterface, ComponentListenerInterface
{
    private array $global = [];
    private array $ready  = [];

    public function getDocument(Document $document) : Document
    {
        if (0 === count($this->global) && 0 === count($this->ready)) {
            return $document;
        }
        $script = new Script();
        foreach ($this->global as $code) {
            $script->addCode($code);
        }
        if (count($this->ready)) {
            $script->addCode("function ready(fn){if(document.readyState!='loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn);}}");
        }
        foreach ($this->ready as $code) {
            $script->addCode($code);
        }
        $document->getChild('body')->addChild($script);
        return $document;
    }

    public function handleComponentEvent(Event $event) : void
    {
        switch ($event->getType()) {
            case 'global-script':
                $this->global[$event->getId()] = $event->getData();
                return;
            case 'ready-script':
                $this->ready[$event->getId()] = $event->getData();
                return;
        }
    }
}
