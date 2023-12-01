<?php

declare(strict_types=1);
namespace Flipsite;

use Flipsite\Builders\DocumentBuilder;


final class Flipsite
{
    public function __construct(protected SiteData $siteData)
    {
    }

    public function render() : string
    {
        $documentBuilder = new DocumentBuilder();
        $document = $documentBuilder->getDocument();

        return $document->render();
    }
}
