<?php

declare(strict_types=1);
namespace Flipsite;

use Flipsite\Components\AbstractElement;
use Flipsite\Builders\ComponentBuilder;
use Flipsite\Builders\CustomCodeBuilder;
use Flipsite\Builders\DocumentBuilder;
use Flipsite\Builders\FaviconBuilder;
use Flipsite\Builders\FontBuilder;
use Flipsite\Builders\IntegrationsBuilder;
use Flipsite\Builders\MetaBuilder;
use Flipsite\Builders\PreloadBuilder;
use Flipsite\Builders\ScriptBuilder;
use Flipsite\Builders\StyleBuilder;
use Flipsite\Builders\SvgBuilder;
use Flipsite\Components\Document;
use Flipsite\Data\SiteDataInterface;
use Flipsite\Utils\Path;
use Flipsite\Utils\Plugins;
use Flipsite\Utils\Robots;
use Flipsite\Utils\Sitemap;
use voku\helper\HtmlMin;

abstract class AbstractDocumentCallback
{
    public function __invoke(Document $document): Document
    {
        return $this->process($document);
    }

    protected function process(AbstractElement $element): AbstractElement
    {
        if ($element->childCount() > 0) {
            foreach ($element->getChildren() as &$child) {
                if (!in_array($child->getTag(), ['defs', 'head'])) {
                    $child = $this->process($child);
                }
            }
        }
        $element = $this->processElement($element);
        return $element;
    }

    abstract protected function processElement(AbstractElement $element): AbstractElement;
}

class OpenToggleCallback extends AbstractDocumentCallback
{
    protected function processElement(AbstractElement $element): AbstractElement
    {
        if ($element->getAttribute('data-toggle-target') && !$this->hasOpenStlyes($element)) {
            $element->setAttribute('data-toggle-target', null);
            $element->setAttribute('onmouseleave', null);
            $element->setAttribute('onmouseenter', null);
        }
        return $element;
    }

    private function hasOpenStlyes(AbstractElement $element) : bool
    {
        $classes = $element->getClasses();
        if (strpos($classes, 'open:') !== false) {
            return true;
        }
        foreach ($element->getChildren() as $child) {
            if ($this->hasOpenStlyes($child)) {
                return true;
            }
        }
        return false;
    }
}

final class Flipsite
{
    private array $callbacks = [];

    public function __construct(protected EnvironmentInterface $environment, protected SiteDataInterface $siteData, protected ?Plugins $plugins = null)
    {
        $this->callbacks[] = new OpenToggleCallback();
    }

    public function getDocument(string $rawPath): Document
    {
        $path = new Path(
            $rawPath,
            $this->siteData->getDefaultLanguage(),
            $this->siteData->getLanguages(),
            $this->siteData->getSlugs(),
        );
        $page = $path->getPage();

        // Create builders
        $appearance = $this->siteData->getAppearance($path->getPage());

        $componentBuilder = new ComponentBuilder(
            $this->environment,
            $this->siteData,
            $path
        );

        $documentBuilder = new DocumentBuilder(
            $componentBuilder,
            $path->getLanguage(),
            $this->siteData->getHtmlStyle($path->getPage()),
            $this->siteData->getBodyStyle($path->getPage()),
            $appearance
        );
        $metaBuilder = new MetaBuilder(
            $this->environment,
            $this->siteData,
            $path
        );
        $scriptBuilder  = new ScriptBuilder();
        $faviconBuilder = new FaviconBuilder($this->environment, $this->siteData);
        $perloadBuilder = new PreloadBuilder();
        $styleBuilder   = new StyleBuilder(
            $this->siteData->getColors(),
            $this->siteData->getFonts(),
            $this->siteData->getThemeSettings(),
            $this->environment->minimizeCss()
        );

        $svgBuilder = new SvgBuilder();

        // Add listeners
        $componentBuilder->addListener($svgBuilder);
        $componentBuilder->addListener($scriptBuilder);
        $componentBuilder->addListener($perloadBuilder);
        $styleBuilder->addListener($scriptBuilder);

        $document = $documentBuilder->getDocument();

        if (null === $page) {
            return $document;
        } else {
            $sections = $this->siteData->getSections($path->getPage(), $path->getLanguage());
        }

        $globalVars = $this->getGlobalVars($this->siteData->getSocial());

        foreach ($sections as $sectionId => $sectionData) {
            if ($this->plugins) {
                $sectionData = $this->plugins->run('section', $sectionData);
            }
            $parentDataSource = array_merge($sectionData['_pageDataSource'] ?? [], $globalVars);
            unset($sectionData['_pageDataSource']);
            $section = $componentBuilder->build('group', $sectionData, [], ['appearance' => $appearance, 'parentDataSource' => $parentDataSource]);
            $document->getChild('body')->addChild($section);
        }

        $document = $svgBuilder->getDocument($document);
        $document = $metaBuilder->getDocument($document);
        $document = $faviconBuilder->getDocument($document);
        $document = $perloadBuilder->getDocument($document);
        $fonts    = $this->siteData->getFonts();
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

        // Custom HTML
        $customCodeBuilder = new CustomCodeBuilder($path->getPage(), $this->siteData, $scriptBuilder);
        $document          = $customCodeBuilder->getDocument($document);
        $document          = $scriptBuilder->getDocument($document);

        // Cleanup
        foreach ($this->callbacks as $callback) {
            $document = $callback($document);
        }

        return $document;
    }

    public function getRedirect(string $rawPath): ?string
    {
        $redirects = $this->siteData->getRedirects();
        if (!$redirects) {
            return null;
        }
        foreach ($redirects as $redirect) {
            if ($redirect['from'] === $rawPath) {
                return $this->environment->getAbsoluteUrl($redirect['to']);
            }
        }

        return null;
    }

    public function render(string $rawPath): string
    {
        switch ($rawPath) {
            case 'robots.txt': return $this->renderRobots();
            case 'sitemap.xml': return $this->renderSitemap();
        }
        $document = $this->getDocument($rawPath);
        $html     = $document->render();
        if ($this->environment->minimizeHtml()) {
            $html = $this->minimizeHtml($html);
        }
        return $html;
    }

    public function renderRobots(): string
    {
        $robots = new Robots($this->environment);
        return (string)$robots;
    }

    public function renderSitemap(): string
    {
        $sitemap = new Sitemap($this->environment, $this->siteData);
        return (string)$sitemap;
    }

    private function minimizeHtml(string $html): string
    {
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

    private function getGlobalVars(?array $social): array
    {
        $globalVars = [
            'site.name'      => $this->siteData->getName(),
            'copyright.year' => '<span data-copyright>' . date('Y') . '</span>'
        ];
        foreach ($social as $type => $handle) {
            $globalVars['social.'.$type] = $handle;
        }
        return $globalVars;
    }
}
