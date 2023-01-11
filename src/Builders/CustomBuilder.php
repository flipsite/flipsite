<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Custom;
use Flipsite\Utils\CustomHtmlParser;

class CustomBuilder implements BuilderInterface
{
    private string $page;
    private CustomHtmlParser $parser;
    public function __construct(string $page, string $customHtmlfilePath)
    {
        $this->page = $page;
        $customHtml = file_get_contents($customHtmlfilePath);
        $this->parser = new CustomHtmlParser($customHtml);
    }

    public function getDocument(Document $document): Document
    {
        $headStart = $this->parser->getHeadStart($this->page);
        if ($headStart) {
            $custom = new Custom($headStart);
            $document->getChild('head')->prependChild($custom);
        }

        $headEnd = $this->parser->getHeadEnd($this->page);
        if ($headEnd) {
            $custom = new Custom($headEnd);
            $document->getChild('head')->addChild($custom);
        }

        $bodyStart = $this->parser->getBodyStart($this->page);
        if ($bodyStart) {
            $custom = new Custom($bodyStart);
            $document->getChild('body')->prependChild($custom);
        }

        $bodyEnd = $this->parser->getBodyEnd($this->page);
        if ($bodyEnd) {
            $custom = new Custom($bodyEnd);
            $document->getChild('body')->addChild($custom);
        }
        return $document;
    }
}
