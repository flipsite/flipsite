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
        $this->document->getChild('body')->addChild(new Element('svg', true));
        foreach ($this->sections as $area => $sections) {
            if (null === $this->layout) {
                foreach ($sections as $section) {
                    $this->document->getChild('body')->addChild($section);
                }
            } else {
                foreach ($sections as $section) {
                    $this->layout->getChild($area)->addChild($section);
                }
            }
        }
        return $this->document;
    }

    public function addLayout(?array $layoutData = null) : void
    {
        if (null === $layoutData) {
            return;
        }
        $this->layout = new Element('div');
        $this->layout->addStyle($layoutData['style']['container'] ?? []);
        foreach ($layoutData['areas'] as $area) {
            $el = new Element('div');
            $el->addStyle($layoutData['style'][$area] ?? []);
            $this->layout->addChild($el, $area);
        }
        $this->document->getChild('body')->addChild($this->layout);
    }

    public function addSection(AbstractElement $section, string $area = 'default') : void
    {
        if (!isset($this->sections[$area])) {
            $this->sections[$area] = [];
        }
        $this->sections[$area][] = $section;
    }
}
