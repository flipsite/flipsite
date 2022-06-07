<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Components\Script;

class AnalyticsBuilder implements BuilderInterface
{
    private ?string $gtm        = null;
    private ?string $ga         = null;
    private ?string $metaPixel  = null;
    private ?string $cookieBot  = null;

    public function __construct(private bool $isLive, array $integrations)
    {
        if (isset($integrations['google']['tagManager'])) {
            $this->gtm = $integrations['google']['tagManager'];
        }
        if (isset($integrations['google']['analytics'])) {
            $this->ga = $integrations['google']['analytics'];
        }
        if (isset($integrations['meta']['pixel'])) {
            $this->metaPixel = (string)$integrations['meta']['pixel'];
        }
        if (isset($integrations['cookieBot'])) {
            $this->cookieBot = $integrations['cookieBot'];
        }
    }

    public function getDocument(Document $document) : Document
    {
        // Google
        if ($this->isLive && $this->gtm) {
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

        if ($this->isLive && $this->ga) {
            $script = new Element('script', true);
            $script->setAttribute('async', true);
            $script->setAttribute('src', 'https://www.googletagmanager.com/gtag/js?id='.$this->ga);
            $jsCode       = file_get_contents(__DIR__.'/googleAnalytics.js');
            $jsCode       = str_replace('UA-XXXX-1', $this->ga, $jsCode);
            $inlineScript = new Script();
            $inlineScript->setContent($jsCode);
            if (str_starts_with($this->ga, 'UA-')) {
                $document->getChild('body')->addChild($script);
                $document->getChild('body')->addChild($inlineScript);
            } elseif (str_starts_with($this->ga, 'G-')) {
                $document->getChild('head')->prependChild($inlineScript);
                $document->getChild('head')->prependChild($script);
            }
        } elseif ($this->ga) {
            $script = new Script();
            $script->setContent('function gtag(a,b,c){console.log(b,c)}');
            $document->getChild('body')->addChild($script);
        }

        // Meta
        if ($this->isLive && $this->metaPixel) {
            $jsCode       = file_get_contents(__DIR__.'/metaPixel.js');
            $jsCode       = str_replace('XXXX', $this->metaPixel, $jsCode);
            $inlineScript = new Script();
            $inlineScript->setContent($jsCode);
            $document->getChild('head')->addChild($inlineScript);
            $noScript = new Script();
            $noScript->setTag('noscript');
            $noScript->setContent('<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.$this->metaPixel.'&ev=PageView&noscript=1"/>');
            $document->getChild('head')->addChild($noScript);
        } elseif ($this->metaPixel) {
            $script = new Script();
            $script->setContent('function fbq(a,b,c){console.log(a,b,c)}');
            $document->getChild('body')->addChild($script);
        }

        // Cookiebot
        if ($this->isLive && $this->cookieBot) {
            $script = new Script();
            $script->setAttribute('id', 'Cookiebot');
            $script->setAttribute('src', 'https://consent.cookiebot.com/uc.js');
            $script->setAttribute('data-cbid', $this->cookieBot);
            $script->setAttribute('type', 'text/javascript');
            $script->setAttribute('data-blockingmode', 'auto');
            $document->getChild('head')->addChild($script);
        }
        return $document;
    }
}
