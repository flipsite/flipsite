<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Data\Reader;
use Flipsite\Enviroment;
use Flipsite\Utils\Language;
use Flipsite\Utils\Path;

abstract class AbstractDocumentBuilder
{
    protected Document $document;
    protected Enviroment $enviroment;
    protected Reader $reader;
    protected Path $path;

    public function __construct(Enviroment $enviroment, Reader $reader, Path $path)
    {
        $this->enviroment = $enviroment;
        $this->reader     = $reader;
        $this->path       = $path;
        $this->reset($path->getLanguage());
    }

    abstract public function getDocument();

    abstract public function addSection(?AbstractElement $section);

    private function reset(Language $language) : void
    {
        $this->document = new Document();
        $this->document->setAttribute('lang', (string) $language);
        $head = new Element('head');
        $head->addChild(new Element('title', true), 'title');

        $charset = new Element('meta', true, true);
        $charset->setAttribute('charset', 'utf-8');
        $head->addChild($charset);

        $viewport = new Element('meta', true, true);
        $viewport->setAttribute('name', 'viewport');
        $viewport->setAttribute('content', 'width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover');
        $head->addChild($viewport);

        $this->document->addChild($head, 'head');
        $this->document->addChild(new Element('body'), 'body');
    }
}
