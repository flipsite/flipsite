<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Document;
use Flipsite\Components\Event;
use Flipsite\Components\InlineScript;

class ScriptBuilder implements BuilderInterface, ComponentListenerInterface
{
    private array $global = [];
    private array $ready  = [];

    public function __construct(private string $hash, private string $basePath, private bool $sw = false)
    {
    }

    public function getDocument(Document $document) : Document
    {
        if (0 === count($this->global) && 0 === count($this->ready)) {
            return $document;
        }
        $script = new InlineScript();
        if (count($this->ready)) {
            $script->addCode("function ready(fn){if(document.readyState!='loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn);}}");
        }
        $document->getChild('head')->prependChild($script);

        $script = new InlineScript();
        foreach ($this->global as $code) {
            $script->addCode($code);
        }
        if ($this->sw) {
            $script->addCode('window.addEventListener("load",()=>{if ("serviceWorker" in navigator){navigator.serviceWorker.register("'.$this->basePath.'/sw.'.$this->hash.'.js");}});');
        }
        foreach ($this->ready as $code) {
            if ($code) $script->addCode($code);
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
