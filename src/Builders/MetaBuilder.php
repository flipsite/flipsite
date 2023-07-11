<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Components\AbstractElement;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Data\Reader;
use Flipsite\Environment;
use Flipsite\Utils\Path;

class MetaBuilder implements BuilderInterface
{
    private ImageHandler $imageHandler;
    private string $h1 = '404';

    public function __construct(private Environment $environment, private Reader $reader, private Path $path)
    {
        $this->imageHandler = new ImageHandler(
            $environment->getAssetSources(),
            $environment->getImgDir(),
            $environment->getImgBasePath(),
        );
    }

    public function getDocument(Document $document): Document
    {
        $elements = [];
        $server   = $this->environment->getServer(true);
        $language = $this->path->getLanguage();
        $page     = $this->path->getPage();
        $slug     = $this->reader->getSlugs()->getSlug($page, $language);
        $trailingSlash = $this->environment->hasTrailingSlash() ? '/' : '';

        $elements[] = $this->meta('canonical', $server.'/'.$slug.$trailingSlash);
        if (count($this->reader->getLanguages()) > 1) {
            foreach ($this->reader->getLanguages() as $l) {
                if (!$language->isSame($l)) {
                    $el = new Element('meta', true, true);
                    $el->setAttribute('rel', 'alternate');
                    $slug = $this->reader->getSlugs()->getSlug($page, $l);
                    $el->setAttribute('href', $server.'/'.$slug.$trailingSlash);
                    $el->setAttribute('hreflang', (string)$l);
                    $elements[] = $el;
                }
            }
        }

        $name  = $this->reader->get('name', $language);
        $meta  = $this->reader->getMeta($this->path->getPage(), $language);

        $title = $meta['title'];

        $document->setAttribute('prefix', 'og: https://ogp.me/ns#', true);
        $document->getChild('head')->getChild('title')->setContent($title);

        // HTML meta tags
        $elements[] = $this->meta('description', $meta['description'] ?? null);

        // Facebook opengraph tags
        $elements[] = $this->og('og:title', $title);
        $elements[] = $this->og('og:description', $meta['description'] ?? null);

        $active = $this->path->getPage();
        $page   = $this->reader->getSlugs()->getPath($active, $language, $active);

        if ($meta['share']) {
            $image      = $this->imageHandler->getContext($meta['share'], ['width' => 1200, 'height' => 630]);
            $src        = $image->getSrc();
            if (!str_starts_with($src, 'http')) {
                $src = $this->environment->getServer(false).$src;
            }
            $elements[] = $this->og('og:image', $src);
        }

        $elements[] = $this->og('og:url', trim($server . $page, '/').$trailingSlash);
        $elements[] = $this->og('og:site_name', $name);
        $elements[] = $this->og('og:type', 'website');

        // Twitter meta
        $elements[] = $this->meta('twitter:card', 'summary_large_image');
        $elements[] = $this->meta('twitter:image:alt', $title);

        // Theme color
        $themeColor = $this->reader->get('pwa.themeColor') ?? $this->reader->get('theme.colors.primary');
        $elements[] = $this->meta('theme-color', $themeColor);

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
