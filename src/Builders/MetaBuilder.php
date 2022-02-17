<?php

declare(strict_types=1);
namespace Flipsite\Builders;

use Flipsite\Assets\ImageHandler;
use Flipsite\Components\AbstractElement;
use Flipsite\Components\ComponentListenerInterface;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use Flipsite\Components\Event;
use Flipsite\Data\Reader;
use Flipsite\Enviroment;
use Flipsite\Utils\Path;

class MetaBuilder implements BuilderInterface, ComponentListenerInterface
{
    private Enviroment $enviroment;
    private Reader $reader;
    private Path $path;
    private ImageHandler $imageHandler;

    public function __construct(Enviroment $enviroment, Reader $reader, Path $path)
    {
        $this->enviroment   = $enviroment;
        $this->reader       = $reader;
        $this->path         = $path;
        $this->imageHandler = new ImageHandler(
            $enviroment->getAssetSources(),
            $enviroment->getImgDir(),
            $enviroment->getImgBasePath(),
        );
    }

    public function getDocument(Document $document) : Document
    {
        $language = $this->path->getLanguage();
        $name     = $this->reader->get('name', $language);
        $meta     = $this->reader->getMeta($this->path->getPage(), $language);

        $title    = $meta['title'] ?? $name;

        $document->setAttribute('prefix', 'og: https://ogp.me/ns#', true);
        $document->getChild('head')->getChild('title')->setContent($title);

        $elements = [];

        // HTML meta tags
        $elements[] = $this->meta('description', $meta['description'] ?? null);
        $elements[] = $this->meta('keywords', $meta['keywords']);
        $elements[] = $this->meta('author', $meta['author'] ?? null);
        $elements[] = $this->meta('generator', $meta['generator'] ?? null);

        // Facebook opengraph tags
        $elements[] = $this->og('og:title', $title);
        $elements[] = $this->og('og:description', $meta['description'] ?? null);

        $server = $this->enviroment->getServer(true);
        $active = $this->path->getPage();
        $page   = $this->reader->getSlugs()->getPath($active, $language, $active);

        $share = $meta['share'] ?? $this->reader->get('share');
        if ($share) {
            $image      = $this->imageHandler->getContext($share, ['width' => 1200, 'height' => 630]);
            $elements[] = $this->og('og:image', $this->enviroment->getServer(false) . $image->getSrc());
        }

        $elements[] = $this->og('og:url', trim($server . $page, '/'));
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

    public function handleComponentEvent(Event $event) : void
    {
    }

    private function meta(string $name, ?string $content) : ?AbstractElement
    {
        if (null === $content) {
            return null;
        }
        $el = new Element('meta', true, true);
        $el->setAttribute('name', $name);
        $el->setAttribute('content', str_replace("\n", '', $content));
        return $el;
    }

    private function og(string $property, ?string $content) : ?AbstractElement
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
