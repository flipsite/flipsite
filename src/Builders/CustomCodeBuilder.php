<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\CustomCode;
use Flipsite\Data\SiteDataInterface;
use Flipsite\Builders\EventListenerInterface;
use Flipsite\Builders\Event;

class CustomCodeBuilder implements BuilderInterface
{
    private CustomHtmlParser $parser;

    public function __construct(private string $page, private SiteDataInterface $siteData, private EventListenerInterface $listener)
    {
    }

    public function getDocument(Document $document): Document
    {
        $headStart = $this->siteData->getCode('headStart', $this->page, true);
        if ($headStart) {
            $custom = new CustomCode($headStart);
            $document->getChild('head')->prependChild($custom);
            if (strpos($custom->getHtml(), 'ready(()=>{') !== false) {
                $this->listener->handleEvent(new Event('ready-script', 'custom', ''));
            }
        }

        $headEnd = $this->siteData->getCode('headEnd', $this->page, true);
        if ($headEnd) {
            $custom = new CustomCode($headEnd);
            $document->getChild('head')->addChild($custom);
            if (strpos($custom->getHtml(), 'ready(()=>{') !== false) {
                $this->listener->handleEvent(new Event('ready-script', 'custom', ''));
            }
        }

        $bodyStart = $this->siteData->getCode('bodyStart', $this->page, true);
        if ($bodyStart) {
            $custom = new CustomCode($bodyStart);
            $document->getChild('body')->prependChild($custom);
            if (strpos($custom->getHtml(), 'ready(()=>{') !== false) {
                $this->listener->handleEvent(new Event('ready-script', 'custom', ''));
            }
        }

        $bodyEnd = $this->siteData->getCode('bodyEnd', $this->page, true);
        if ($bodyEnd) {
            $custom = new CustomCode($bodyEnd);
            $document->getChild('body')->addChild($custom);
            if (strpos($custom->getHtml(), 'ready(()=>{') !== false) {
                $this->listener->handleEvent(new Event('ready-script', 'custom', ''));
            }
        }
        return $document;
    }
}
