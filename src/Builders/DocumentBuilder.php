<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Document;
use Flipsite\Components\Element;

final class DocumentBuilder extends AbstractDocumentBuilder
{
    private array $sections  = [];
    private ?Element $layout = null;

    public function getDocument() : Document
    {
        $this->document->addStyle($this->reader->get('theme.components.html'));
        $this->document->getChild('body')->addChild(new Element('svg', true));
        foreach ($this->sections as $section) {
            $this->document->getChild('body')->addChild($section);
        }
        return $this->document;
    }

    public function addSection(?AbstractElement $section) : void
    {
        if (null === $section) {
            return;
        }
        $this->sections[] = $section;
    }
}
