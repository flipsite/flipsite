<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\CustomCode;
use Flipsite\Utils\CustomHtmlParser;

class CustomCodeBuilder implements BuilderInterface
{
    private CustomHtmlParser $parser;

    public function __construct(private bool $isLive, private string $page, string $customHtmlfilePath)
    {
        $customHtml   = file_get_contents($customHtmlfilePath);
        $this->parser = new CustomHtmlParser($customHtml);
    }

    public function getDocument(Document $document): Document
    {
        $headStart = $this->parser->get('headStart', $this->page);
        if ($headStart) {
            $custom = new CustomCode($headStart);
            $document->getChild('head')->prependChild($custom);
        }

        $headEnd = $this->parser->get('headEnd', $this->page);
        if ($headEnd) {
            $custom = new CustomCode($headEnd);
            $document->getChild('head')->addChild($custom);
        }

        $bodyStart = $this->parser->get('bodyStart', $this->page);
        if ($bodyStart) {
            $custom = new CustomCode($bodyStart);
            $document->getChild('body')->prependChild($custom);
        }

        $bodyEnd = $this->parser->get('bodyEnd', $this->page);
        if ($bodyEnd) {
            $custom = new CustomCode($bodyEnd);
            $document->getChild('body')->addChild($custom);
        }
        return $document;
    }
}