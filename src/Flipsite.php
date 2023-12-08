<?php

declare(strict_types=1);

namespace Flipsite;

use Flipsite\Builders\DocumentBuilder;
use Flipsite\Data\SiteDataInterface;
// use Flipsite\Builders\CustomCodeBuilder;
use Flipsite\Builders\IntegrationsBuilder;
use Flipsite\Builders\ComponentBuilder;
use Flipsite\Builders\FaviconBuilder;
use Flipsite\Builders\FontBuilder;
use Flipsite\Builders\MetaBuilder;
use Flipsite\Builders\ScriptBuilder;
use Flipsite\Builders\PreloadBuilder;
use Flipsite\Builders\StyleBuilder;
use Flipsite\Builders\SvgBuilder;
use Flipsite\Utils\Path;
use voku\helper\HtmlMin;

final class Flipsite
{
    public function __construct(protected EnvironmentInterface $environment, protected SiteDataInterface $siteData) {}

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
        $metaBuilder = new MetaBuilder(
            $this->environment, 
            $this->siteData, 
            $path
        );
        $scriptBuilder = new ScriptBuilder();
        $faviconBuilder = new FaviconBuilder($this->environment, $this->siteData);
        $perloadBuilder = new PreloadBuilder();
        $styleBuilder = new StyleBuilder(
            $this->siteData->getColors(), 
            $this->siteData->getFonts()
        );
        $componentBuilder = new ComponentBuilder(
            $this->environment, 
            $this->siteData, 
            $path
        );
        $svgBuilder = new SvgBuilder();
        
        // Add listeners
        $componentBuilder->addListener($svgBuilder);
        $componentBuilder->addListener($scriptBuilder);
        $componentBuilder->addListener($perloadBuilder);

        $document = $documentBuilder->getDocument();
        foreach ($this->siteData->getSections($path) as $sectionId => $sectionData) {
            //$sectionData = $plugins->run('section', $sectionData);
            $section = $componentBuilder->build('group', $sectionData, [], ['appearance' => 'light']); // TODO page appearence
            $document->getChild('body')->addChild($section);
        }

        $document = $svgBuilder->getDocument($document);
        $document = $metaBuilder->getDocument($document);
        $document = $faviconBuilder->getDocument($document);
        $document = $perloadBuilder->getDocument($document);
        $fonts = $this->siteData->getFonts();
        if ($fonts) {
            $fontBuilder = new FontBuilder($fonts);
            $document    = $fontBuilder->getDocument($document);
        }
        $document = $styleBuilder->getDocument($document);
        
        // Integrations
        $integrations = $this->siteData->getIntegrations();
        if (null !== $integrations) {
            $analyticsBuilder = new IntegrationsBuilder($this->environment->isProduction(), $integrations);
            $document         = $analyticsBuilder->getDocument($document);
        }
    // $document->getChild('head')->addChild(new Flipsite\Components\CustomCode('<style></style>'));

    // // Custom HTML
    // $customCodeFile = $environment->getSiteDir().'/custom.html';
    // if (file_exists($customCodeFile)) {
    //     $customCodeBuilder = new CustomCodeBuilder($environment->isLive(), $page, $customCodeFile, $scriptBuilder);
    //     $document          = $customCodeBuilder->getDocument($document);
    // }

        $document = $scriptBuilder->getDocument($document);

        $html = $document->render();
        if ($this->environment->minimizeHtml()) {
            $html = $this->minimizeHtml($html);
        }
        return $html;
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
    private function minimizeHtml(string $html) : string {
        $htmlMin = new HtmlMin();
        $htmlMin->doOptimizeViaHtmlDomParser();               // optimize html via "HtmlDomParser()"
        $htmlMin->doRemoveComments();                         // remove default HTML comments (depends on "doOptimizeViaHtmlDomParser(true)")
        $htmlMin->doSumUpWhitespace();                        // sum-up extra whitespace from the Dom (depends on "doOptimizeViaHtmlDomParser(true)")
        $htmlMin->doRemoveWhitespaceAroundTags();             // remove whitespace around tags (depends on "doOptimizeViaHtmlDomParser(true)")
        $htmlMin->doOptimizeAttributes();                     // optimize html attributes (depends on "doOptimizeViaHtmlDomParser(true)")
        $htmlMin->doRemoveHttpPrefixFromAttributes();         // remove optional "http:"-prefix from attributes (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveHttpsPrefixFromAttributes();        // remove optional "https:"-prefix from attributes (depends on "doOptimizeAttributes(true)")
        $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes(); // keep "http:"- and "https:"-prefix for all external links 
        $htmlMin->doRemoveDefaultAttributes();                // remove defaults (depends on "doOptimizeAttributes(true)" | disabled by default)
        $htmlMin->doRemoveDeprecatedAnchorName();             // remove deprecated anchor-jump (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveDeprecatedScriptCharsetAttribute(); // remove deprecated charset-attribute - the browser will use the charset from the HTTP-Header, anyway (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveDeprecatedTypeFromScriptTag();      // remove deprecated script-mime-types (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveDeprecatedTypeFromStylesheetLink(); // remove "type=text/css" for css links (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveDeprecatedTypeFromStyleAndLinkTag(); // remove "type=text/css" from all links and styles
        $htmlMin->doRemoveDefaultMediaTypeFromStyleAndLinkTag(); // remove "media="all" from all links and styles
        $htmlMin->doRemoveDefaultTypeFromButton();            // remove type="submit" from button tags 
        $htmlMin->doRemoveEmptyAttributes();                  // remove some empty attributes (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveValueFromEmptyInput();              // remove 'value=""' from empty <input> (depends on "doOptimizeAttributes(true)")
        $htmlMin->doSortHtmlAttributes();                     // sort html-attributes, for better gzip results (depends on "doOptimizeAttributes(true)")
        $htmlMin->doRemoveSpacesBetweenTags();                // remove more (aggressive) spaces in the dom (disabled by default)
        $htmlMin->doRemoveOmittedQuotes();                    // remove quotes e.g. class="lall" => class=lall
        $htmlMin->doRemoveOmittedHtmlTags();                  // remove ommitted html tags e.g. <p>lall</p> => <p>lall 
        return $htmlMin->minify($html); 
    }
}
