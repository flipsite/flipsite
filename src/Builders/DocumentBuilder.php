<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Components\AbstractElement;
use Flipsite\Components\Document;
use Flipsite\Components\Element;

use Flipsite\Utils\Language;

final class DocumentBuilder
{
    public function __construct(private ?Language $language = null, private array $htmlStyle = [], private array $bodyStyle = []) {

    }

    public function getDocument() : Document
    {
        // <html>
        $document = new Document();
        $document->setAttribute('lang', (string)($this->language ?? 'en'));
        $document->addStyle($this->htmlStyle);

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

        // <bodu>
        $body = new Element('body');
        $body->addStyle($this->bodyStyle);
        $body->addChild(new Element('svg', true));
        $document->addChild($body, 'body');

        return $document;
    }
}
