<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Components\Script;

class AnalyticsBuilder implements BuilderInterface
{
    private ?string $gtm = null;
    private ?string $ga  = null;

    public function __construct(array $integrations)
    {
        if (isset($integrations['google']['tagManager'])) {
            $this->gtm = $integrations['google']['tagManager'];
        }
        if (isset($integrations['google']['analytics'])) {
            $this->ga = $integrations['google']['analytics'];
        }
    }

    public function getDocument(Document $document) : Document
    {
        if ($this->gtm) {
            $jsCode = file_get_contents(__DIR__.'/googleTagManager.js');
            $jsCode = str_replace('GTM-XXXX', $this->gtm, $jsCode);
            $script = new Script();
            $script->setContent($jsCode);
            $document->getChild('head')->prependChild($script);

            $noscript = new Element('noscript');
            $iframe   = new Element('iframe', true);
            $iframe->setAttribute('src', 'https://www.googletagmanager.com/ns.html?id='.$this->gtm);
            $iframe->setAttribute('height', '0');
            $iframe->setAttribute('width', '0');
            $iframe->setAttribute('style', 'display:none;visibility:hidden');
            $noscript->addChild($iframe);
            $document->getChild('body')->prependChild($noscript);
        }

        if ($this->ga) {
            $script = new Element('script', true);
            $script->setAttribute('async', true);
            $script->setAttribute('src', 'https://www.googletagmanager.com/gtag/js?id='.$this->ga);
            $jsCode = file_get_contents(__DIR__.'/googleAnalytics.js');
            $jsCode = str_replace('UA-XXXX-1', $this->ga, $jsCode);
            $inlineScript = new Script();
            $inlineScript->setContent($jsCode);
            if (str_starts_with($this->ga, 'UA-')) {
                $document->getChild('body')->addChild($script);
                $document->getChild('body')->addChild($inlineScript);
            } elseif (str_starts_with($this->ga, 'G-')) {
                $document->getChild('head')->prependChild($inlineScript);
                $document->getChild('head')->prependChild($script);
            }
        }
        return $document;
    }
}
