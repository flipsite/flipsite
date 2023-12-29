<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Assets\Assets;
use Flipsite\Components\AbstractElement;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Data\SiteDataInterface;
use Flipsite\EnvironmentInterface;
use Flipsite\Utils\Path;

class MetaBuilder implements BuilderInterface
{
    private Assets $assets;

    public function __construct(private EnvironmentInterface $environment, private SiteDataInterface $siteData, private Path $path)
    {
        $this->assets = new Assets($environment->getAssetSources());
    }

    public function getDocument(Document $document): Document
    {
        $elements = [];
        $language = $this->path->getLanguage();
        $page     = $this->path->getPage();
        $slug     = $this->siteData->getSlugs()->getSlug($page, $language);
        
        $canonical = $this->environment->getAbsoluteUrl($slug);

        $elements[] = $this->meta('canonical', $canonical);
        if (count($this->siteData->getLanguages()) > 1) {
            foreach ($this->siteData->getLanguages() as $l) {
                if (!$language->isSame($l)) {
                    $el = new Element('meta', true, true);
                    $el->setAttribute('rel', 'alternate');
                    $slug = $this->siteData->getSlugs()->getSlug($page, $l);
                    $el->setAttribute('href', $this->environment->getAbsoluteUrl($slug));
                    $el->setAttribute('hreflang', (string)$l);
                    $elements[] = $el;
                }
            }
        }

        $name  = $this->siteData->getName();
        $meta  = $this->siteData->getMeta($this->path->getPage(), $language);

        $title = $meta['title'] ?? 'Flipsite';

        $document->setAttribute('prefix', 'og: https://ogp.me/ns#', true);
        $document->getChild('head')->getChild('title')->setContent($title);

        // HTML meta tags
        $elements[] = $this->meta('description', $meta['description'] ?? null);

        // Generator
        $elements[] = $this->meta('generator', $this->environment->getGenerator());

        // Facebook opengraph tags
        $elements[] = $this->og('og:title', $title);
        $elements[] = $this->og('og:description', $meta['description'] ?? null);

        if (isset($meta['share']) && $imageAttributes = $this->assets->getImageAttributes($meta['share'], ['width' => 1200, 'height' => 630, 'webp' => false])) {
            $src = $imageAttributes->getSrc();
            $elements[] = $this->og('og:image', $this->environment->getAbsoluteSrc($src, true));
        }

        $elements[] = $this->og('og:url', $this->environment->getAbsoluteUrl($slug));
        $elements[] = $this->og('og:site_name', $name);
        $elements[] = $this->og('og:type', 'website');

        // Twitter meta
        $elements[] = $this->meta('twitter:card', 'summary_large_image');
        $elements[] = $this->meta('twitter:image:alt', $title);

        // Theme color TODO
        // $themeColor = $this->siteData->get('pwa.themeColor') ?? $this->siteData->get('theme.colors.primary');
        // $elements[] = $this->meta('theme-color', $themeColor);

        foreach ($elements as $el) {
            if (null !== $el) {
                $document->getChild('head')->addChild($el);
            }
        }

        return $document;
    }

    private function meta(string $name, ?string $content): ?AbstractElement
    {
        if (null === $content) {
            return null;
        }
        $el = new Element('meta', true, true);
        $el->setAttribute('name', $name);
        $el->setAttribute('content', str_replace("\n", '', $content));
        return $el;
    }

    private function og(string $property, ?string $content): ?AbstractElement
    {
        if (null === $content) {
            return null;
        }
        $el = new Element('meta', true, true);
        $el->setAttribute('property', $property);
        $el->setAttribute('content', str_replace("\n", '', $content));
        return $el;
    }
}
