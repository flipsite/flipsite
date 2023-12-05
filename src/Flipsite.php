<?php

declare(strict_types=1);

namespace Flipsite;

use Flipsite\Builders\DocumentBuilder;
use Flipsite\Data\SiteDataInterface;
// use Flipsite\Builders\CustomCodeBuilder;
// use Flipsite\Builders\IntegrationsBuilder;
use Flipsite\Builders\ComponentBuilder;
// use Flipsite\Builders\FaviconBuilder;
// use Flipsite\Builders\FontBuilder;
// use Flipsite\Builders\MetaBuilder;
// use Flipsite\Builders\ScriptBuilder;
// use Flipsite\Builders\PreloadBuilder;

use Flipsite\Builders\StyleBuilder;
use Flipsite\Builders\SvgBuilder;
use Flipsite\AbstractEnvironment;

use Flipsite\Utils\Path;

final class Flipsite
{
    public function __construct(protected AbstractEnvironment $environment, protected SiteDataInterface $siteData) {}

    public function render(string $rawPath): string
    {
        switch ($rawPath) {
            case 'robots.txt': return $this->renderRobots();
            case 'sitemap.xml': return $this->renderSitemap();
        }
        $path = new Path(
            $rawPath,
            $this->siteData->getDefaultLanguage(),
            $this->siteData->getLanguages(),
            $this->siteData->getSlugs(),
        );
        
        // Create builders
        $documentBuilder = new DocumentBuilder(
            $path->getLanguage(),
            $this->siteData->getHtmlStyle(),
            $this->siteData->getBodyStyle($path->getPage()),
        );
        $styleBuilder = new StyleBuilder(/*$this->siteData->getColors(), $this->siteData->getFonts()*/);

        $svgBuilder = new SvgBuilder();
        $componentBuilder = new ComponentBuilder($this->environment, $this->siteData, $path);
        // $metaBuilder = new MetaBuilder($environment, $reader, $path);
        // $faviconBuilder = new FaviconBuilder($environment, $reader);
        // $scriptBuilder = new ScriptBuilder(
        //     $reader->getHash(),
        //     $environment->getBasePath(),
        //     (bool)$reader->get('offline')
        // );
        // $componentBuilder->addListener($scriptBuilder);
        // $perloadBuilder = new PreloadBuilder();
        // $componentBuilder->addListener($perloadBuilder);

        $document = $documentBuilder->getDocument();


        foreach ($this->siteData->getSections($path) as $sectionId => $sectionData) {
            //$sectionData = $plugins->run('section', $sectionData);
            $section = $componentBuilder->build('group', $sectionData, [], ['appearance' => 'light']); // TODO page appearence
            $document->getChild('body')->addChild($section);
        }

        $document = $styleBuilder->getDocument($document);
        $document = $svgBuilder->getDocument($document);


        return $document->render();
    }
    public function renderRobots(): string
    {
        //     $environment = $this->get('environment');
        //     $robots      = new Robots($environment->isLive(), $environment->getServer());
        //     $response->getBody()->write((string) $robots);
        return '';
    }
    public function renderSitemap(): string
    {
        //     $environment = $this->get('environment');
        // $reader      = $this->get('reader');
        // $sitemap     = new Sitemap($environment->getServer(), $reader->getSlugs(), $reader->getHiddenPages(), $environment->hasTrailingSlash());
        // $response->getBody()->write((string) $sitemap);
        // return $response->withHeader('Content-type', 'application/xml');
    }
}
