<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\AbstractElement;
use Flipsite\Components\Element;
use Flipsite\Components\Event;
use Flipsite\Components\ComponentListenerInterface;

class SvgBuilder implements BuilderInterface, ComponentListenerInterface
{
    private $data  = ['123abc' => 'asdasdasd'];
    public function __construct()
    {
        
    }

    public function getDocument(Document $document): Document
    {
        $svg = new Element('svg');
        $svg->setAttribute('version','1.1');
        $svg->setAttribute('xmlns','http://www.w3.org/2000/svg');
        $svg->setAttribute('xmlns:xlink','http://www.w3.org/1999/xlink');
        $svg->setAttribute('style','display:none;');
        
        $defs = new Element('defs');
        foreach ($this->data as $id => $data) {
            $g = new Element('g', true);
            $g->setAttribute('id', $id);
            $g->setContent(trim($data));
            $defs->addChild($g);
        }
        
        $svg->addChild($defs);
        $document->getChild('body')->prependChild($svg);

        return $document;
    }

    public function handleComponentEvent(Event $event) : void
    {
        if ('svg' !== $event->getType()) return;
        print_r($event);
    }
}
