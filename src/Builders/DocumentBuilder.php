<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Document;
use Flipsite\Components\Element;

final class DocumentBuilder extends AbstractDocumentBuilder
{
    private array $sections   = [];
    private ?Element $wrapper = null;

    public function addBodyStyle(array $style) : void
    {
        if (isset($style['wrapper'])) {
            $this->wrapper = new Element('div');
            $this->wrapper->addStyle($style['wrapper']);
            $this->document->getChild('body')->addChild($this->wrapper);
            unset($style['wrapper']);
        }
        $this->document->getChild('body')->addStyle($style);
    }

    public function getDocument() : Document
    {
        $this->document->addStyle($this->reader->get('theme.components.html'));
        $this->document->getChild('body')->addChild(new Element('svg', true));
        foreach ($this->sections as $section) {
            if (null === $this->wrapper) {
                $this->document->getChild('body')->addChild($section);
            } else {
                $this->wrapper->addChild($section);
            }
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
