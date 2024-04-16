<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Components\Script;

class IntegrationsBuilder implements BuilderInterface
{
    private ?string $gtm        = null;
    private ?string $ga         = null;
    private ?string $metaPixel  = null;
    private ?string $plausible  = null;

    public function __construct(private bool $isLive, array $integrations)
    {
        if (isset($integrations['googleTagManager'])) {
            $this->gtm = $integrations['googleTagManager'];
        }
        if (isset($integrations['googleAnalytics'])) {
            $this->ga = $integrations['googleAnalytics'];
        }
        if (isset($integrations['metaPixel'])) {
            $this->metaPixel = (string)$integrations['metaPixel'];
        }
        if (isset($integrations['plausibleAnalytics'])) {
            $this->plausible = (string)$integrations['plausibleAnalytics'];
        }
    }

    public function getDocument(Document $document): Document
    {
        // Google Tag Manager
        if ($this->gtm) {
            $jsCode = file_get_contents(__DIR__.'/googleTagManager.js');
            $jsCode = str_replace('GTM-XXXX', $this->gtm, $jsCode);
            $script = new Script();
            $script->setContent($jsCode);
            $script->commentOut(!$this->isLive, 'Not live environment');
            $document->getChild('head')->prependChild($script);

            $noscript = new Element('noscript');
            $iframe   = new Element('iframe', true);
            $iframe->setAttribute('src', 'https://www.googletagmanager.com/ns.html?id='.$this->gtm);
            $iframe->setAttribute('height', '0');
            $iframe->setAttribute('width', '0');
            $iframe->setAttribute('style', 'display:none;visibility:hidden');
            $noscript->addChild($iframe);
            $noscript->commentOut(!$this->isLive, 'Not live environment');
            $document->getChild('body')->prependChild($noscript);
        }

        // Google Analytics
        if ($this->ga) {
            $script = new Element('script', true);
            $script->setAttribute('async', true);
            $script->setAttribute('src', 'https://www.googletagmanager.com/gtag/js?id='.$this->ga);
            $script->commentOut(!$this->isLive, 'Not live environment');
            $jsCode       = file_get_contents(__DIR__.'/googleAnalytics.js');
            $jsCode       = str_replace('UA-XXXX-1', $this->ga, $jsCode);
            $inlineScript = new Script();
            $inlineScript->setContent($jsCode);
            $inlineScript->commentOut(!$this->isLive, 'Not live environment');
            if (str_starts_with($this->ga, 'UA-')) {
                $document->getChild('body')->addChild($script);
                $document->getChild('body')->addChild($inlineScript);
            } elseif (str_starts_with($this->ga, 'G-')) {
                $document->getChild('head')->prependChild($inlineScript);
                $document->getChild('head')->prependChild($script);
            }
        }

        // Meta Pixel
        if ($this->metaPixel) {
            $jsCode       = file_get_contents(__DIR__.'/metaPixel.js');
            $jsCode       = str_replace('XXXX', $this->metaPixel, $jsCode);
            $inlineScript = new Script();
            $inlineScript->setContent($jsCode);
            $inlineScript->commentOut(!$this->isLive, 'Not live environment');
            $document->getChild('head')->addChild($inlineScript);
            $noScript = new Element('noscript');
            $noScript->setContent('<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.$this->metaPixel.'&ev=PageView&noscript=1"/>');
            $noScript->commentOut(!$this->isLive, 'Not live environment');
            $document->getChild('head')->addChild($noScript);
        }

        // Plausible
        if ($this->plausible) {
            $script = new Element('script', true);
            $script->setAttribute('defer', true);
            $script->setAttribute('src', 'https://plausible.io/js/script.js');
            $script->setAttribute('data-domain', $this->plausible);
            $script->commentOut(!$this->isLive, 'Not live environment');
            $document->getChild('head')->prependChild($script);
        }

        return $document;
    }
}
