<?php

declare(strict_types=1);

namespace Flipsite\Data;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Language;

interface PageNameResolverInterface
{
    public function getName(string $page, Language $language): ?string;
}

class PageNameResolver implements PageNameResolverInterface
{
    private array $resolvers = [];

    public function __construct(array $meta, Slugs $slugs)
    {
        $this->resolvers['meta'] = new MetaNameResolver($meta);
        $this->resolvers['home'] = new HomeNameResolver();
        $this->resolvers['slug'] = new SlugNameResolver($slugs);
    }

    public function getName(string $page, Language $language, array $exclude = []): string
    {
        foreach ($this->resolvers as $type => $resolver) {
            if (in_array($type, $exclude)) {
                continue;
            }
            $name = $resolver->getName($page, $language);
            if ($name) {
                return $name;
            }
        }
        return '['.$page.']';
    }
}

class MetaNameResolver implements PageNameResolverInterface
{
    private array $names = [];

    public function __construct(array $meta)
    {
        foreach ($meta as $page => $metaData) {
            if (isset($metaData['name'])) {
                $this->names[$page] = $metaData['name'];
            }
        }
    }

    public function getName(string $page, Language $language): ?string
    {
        if (isset($this->names[$page])) {
            $name = $this->names[$page];
            if (is_string($name)) {
                return $name;
            }
            // Localization
            $lang = (string)$language;
            if (isset($name[$lang])) {
                return $name[$lang];
            }
            $lang = array_key_first($name);
            if (isset($name[$lang])) {
                return $name[$lang];
            }
        }
        return null;
    }
}

class HomeNameResolver implements PageNameResolverInterface
{
    public function getName(string $page, Language $language): ?string
    {
        if ('home' === $page) {
            switch ((string)$language) {
                case 'sv': return 'Hem';
                case 'fi': return 'Koti';
                default: return 'Home';
            }
        }
        return null;
    }
}

class SlugNameResolver implements PageNameResolverInterface
{
    public function __construct(private Slugs $slugs)
    {
    }

    public function getName(string $page, Language $language): ?string
    {
        $slug = $this->slugs->getSlug($page, $language);
        if (null === $slug) {
            return null;
        }
        $slug = explode('/', $slug);
        $last = array_pop($slug);
        return ucfirst(str_replace('-', ' ', $last));
    }
}
