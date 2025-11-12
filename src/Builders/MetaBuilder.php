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
use Flipsite\Utils\ColorHelper;

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

        if (!$page) {
            return $document;
        }

        if ($slug === 'home' || $slug === '404') {
            $slug = '';
        }

        $name  = $this->siteData->getName();
        $meta  = $this->siteData->getMeta($this->path->getPage(), $language);

        if (isset($meta['canonical'])) {
            $el        = new Element('link', true, true);
            $el->setAttribute('rel', 'canonical');
            $el->setAttribute('href', $meta['canonical']);
            $elements[] = $el;
        } else {
            $canonical = $this->environment->getAbsoluteUrl($slug);
            $el        = new Element('link', true, true);
            $el->setAttribute('rel', 'canonical');
            $el->setAttribute('href', $canonical);
            $elements[] = $el;
            if (count($this->siteData->getLanguages()) > 1) {
                foreach ($this->siteData->getLanguages() as $l) {
                    if (!$language->isSame($l)) {
                        $el = new Element('link', true, true);
                        $el->setAttribute('rel', 'alternate');
                        $langSlug = $this->siteData->getSlugs()->getSlug($page, $l);
                        $el->setAttribute('href', $this->environment->getAbsoluteUrl($langSlug));
                        $el->setAttribute('hreflang', (string)$l);
                        $elements[] = $el;
                    }
                }
            }
        }

        $hidden = $this->siteData->getHiddenPages();
        if (!$this->environment->isProduction() || in_array($slug, $hidden)) {
            $elements[] = $this->meta('robots', 'noindex, nofollow');
        } else {
            $elements[] = $this->meta('robots', 'index, follow');
        }

        $title = $meta['title'] ?? 'Flipsite';

        $document->setAttribute('prefix', 'og: https://ogp.me/ns#', true);
        $document->getChild('head')->getChild('title')->setContent($title);

        // HTML meta tags
        $elements[] = $this->meta('description', $meta['description'] ?? null);

        // Generator
        $elements[] = $this->meta('generator', $this->environment->getGenerator());
        if ($this->environment->compileTimestamp()) {
            $date       = new \DateTime('now', new \DateTimeZone('UTC'));
            $elements[] = $this->meta('compiled', $date->format("Y-m-d\TH:i:s\Z"));
        }
        if ($this->environment->getVersion()) {
            $elements[] = $this->meta('version', $this->environment->getVersion());
        }

        // Facebook opengraph tags
        $elements[] = $this->og('og:title', $title);
        $elements[] = $this->og('og:description', $meta['description'] ?? null);

        if (isset($meta['share']) && $imageAttributes = $this->assets->getImageAttributes($meta['share'], ['width' => 1200, 'height' => 630, 'webp' => false])) {
            $src        = $imageAttributes->getSrc();
            $elements[] = $this->og('og:image', $this->environment->getAbsoluteSrc($src, true));
        }

        $elements[] = $this->og('og:url', $this->environment->getAbsoluteUrl($slug));
        $elements[] = $this->og('og:site_name', $name);
        $elements[] = $this->og('og:type', 'website');

        // Twitter meta
        $elements[] = $this->meta('twitter:card', 'summary_large_image');
        $elements[] = $this->meta('twitter:image:alt', $title);

        // App smart banner
        $appleAppId = $this->siteData->getAppleAppId();
        if ($appleAppId) {
            $tmp   = explode(',', $appleAppId);
            $value = 'app-id='.$tmp[0];
            if (isset($tmp[1])) {
                $value .= ', app-argument='.$tmp[1];
            }
            $elements[] = $this->meta('apple-itunes-app', $value);
        }

        // Theme color
        $color = $this->siteData->getThemeColor();
        if ($color) {
            if ($color[0] === '#') {
                $color = '['.$color.']';
            }
            $themeColor = ColorHelper::getColorString($color, $this->siteData->getColors());
            $elements[] = $this->meta('theme-color', $themeColor);
        }

        // CDN preconnect
        $assetsBasePath = $this->environment->getAssetsBasePath();
        if (str_starts_with($assetsBasePath, 'http')) {
            $pathinfo = pathinfo($assetsBasePath);
            $url      = $pathinfo['dirname'];
            $link     = new Element('link', true, true);
            $link->setAttribute('rel', 'preconnect');
            $link->setAttribute('href', $url);
            $link->setAttribute('crossorigin', true);
            $elements[] = $link;

            $link = new Element('link', true, true);
            $link->setAttribute('rel', 'dns-prefetch');
            $link->setAttribute('href', $url);
            $elements[] = $link;
        }

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
