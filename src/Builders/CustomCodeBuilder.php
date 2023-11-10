<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\CustomCode;
use Flipsite\Utils\CustomHtmlParser;
use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Event;

class CustomCodeBuilder implements BuilderInterface
{
    private CustomHtmlParser $parser;

    public function __construct(private bool $isLive, private string $page, private string $customHtmlfilePath, private ComponentListenerInterface $listener)
    {
        $customHtml   = file_get_contents($customHtmlfilePath);
        $this->parser = new CustomHtmlParser($customHtml);
    }

    public function getDocument(Document $document): Document
    {
        $headStart = $this->parser->get('headStart', $this->page, true);
        if ($headStart) {
            $custom = new CustomCode($headStart);
            $document->getChild('head')->prependChild($custom);
            if (strpos($custom->getHtml(), 'ready(()=>{') !== false) {
                $this->listener->handleComponentEvent(new Event('ready-script', 'custom', ''));
            }
        }

        $headEnd = $this->parser->get('headEnd', $this->page, true);
        if ($headEnd) {
            $custom = new CustomCode($headEnd);
            $document->getChild('head')->addChild($custom);
            if (strpos($custom->getHtml(), 'ready(()=>{') !== false) {
                $this->listener->handleComponentEvent(new Event('ready-script', 'custom', ''));
            }
        }

        $bodyStart = $this->parser->get('bodyStart', $this->page, true);
        if ($bodyStart) {
            $custom = new CustomCode($bodyStart);
            $document->getChild('body')->prependChild($custom);
            if (strpos($custom->getHtml(), 'ready(()=>{') !== false) {
                $this->listener->handleComponentEvent(new Event('ready-script', 'custom', ''));
            }
        }

        $bodyEnd = $this->parser->get('bodyEnd', $this->page, true);
        if ($bodyEnd) {
            $custom = new CustomCode($bodyEnd);
            $document->getChild('body')->addChild($custom);
            if (strpos($custom->getHtml(), 'ready(()=>{') !== false) {
                $this->listener->handleComponentEvent(new Event('ready-script', 'custom', ''));
            }
        }
        return $document;
    }
}
