<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Builders\ComponentBuilder;

use Flipsite\Utils\Language;

final class DocumentBuilder
{
    public function __construct(private ComponentBuilder $componentBuilder, private ?Language $language = null, private array $htmlStyle = [], private array $bodyStyle = [], private string $appearance = 'light') {
    }

    public function getDocument() : Document
    {
        $document = new Document();
        $document->setAttribute('lang', (string)($this->language ?? 'en'));

        $rtl = ['ar', 'he', 'fa', 'ur', 'ps', 'ku'];
        if (null !== $this->language && in_array((string)$this->language, $rtl)) {
            $document->setAttribute('dir', 'rtl');
        } else {
            $document->setAttribute('dir', 'ltr');
        }

        $htmlStyle = \Flipsite\Utils\StyleAppearanceHelper::apply($this->htmlStyle, $this->appearance);
        if (isset($htmlStyle['background'])) {
            $this->componentBuilder->handleBackground($document, $htmlStyle['background']);
            unset($htmlStyle['background']);
        }
        $document->addStyle($htmlStyle);

        // <head>
        $head = new Element('head');

        $head->addChild(new Element('title', true), 'title');

        $charset = new Element('meta', true, true);
        $charset->setAttribute('charset', 'utf-8');
        $head->addChild($charset);

        $httpEquiv = new Element('meta', true, true);
        $httpEquiv->setAttribute('http-equiv', 'X-UA-Compatible');
        $httpEquiv->setAttribute('content', 'IE=edge');
        $head->addChild($httpEquiv);

        $viewport = new Element('meta', true, true);
        $viewport->setAttribute('name', 'viewport');
        $viewport->setAttribute('content', 'width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover');
        $head->addChild($viewport);

        $document->addChild($head, 'head');

        // <body>
        $body = new Element('body');
        $bodyStyle = \Flipsite\Utils\StyleAppearanceHelper::apply($this->bodyStyle, $this->appearance);
        if (isset($bodyStyle['background'])) {
            $this->componentBuilder->handleBackground($body, $bodyStyle['background']);
            unset($bodyStyle['background']);
        }
        $body->addStyle($bodyStyle);
        $document->addChild($body, 'body');

        return $document;
    }
}
