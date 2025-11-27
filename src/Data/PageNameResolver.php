<?php

declare(strict_types=1);
namespace Flipsite\Data;

use Flipsite\Utils\Language;
use Flipsite\Utils\Localization;

interface PageNameResolverInterface
{
    public function getName(string $page, Language $language): ?string;
}

class PageNameResolver implements PageNameResolverInterface
{
    private array $resolvers = [];

    public function __construct(array $languages, array $meta, Slugs $slugs)
    {
        $this->resolvers['meta'] = new MetaNameResolver($meta, $languages);
        $this->resolvers['home'] = new HomeNameResolver();
        $this->resolvers['slug'] = new SlugNameResolver($slugs, $languages);
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

        return '-';
    }
}

class MetaNameResolver implements PageNameResolverInterface
{
    private array $names = [];

    public function __construct(array $meta, private array $languages)
    {
        foreach ($meta as $page => $metaData) {
            if ($metaData['name'] ?? false) {
                $this->names[$page] = $metaData['name'];
            }
        }
    }

    public function getName(string $page, Language $language): ?string
    {
        if (isset($this->names[$page])) {
            $name          = $this->names[$page];
            $localization  = new Localization($this->languages, $name);
            $localizedName = $localization->getValue($language);
            if ($localizedName) {
                return $localizedName;
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
                case 'de': return 'Startseite';
                case 'fr': return 'Accueil';
                case 'es': return 'Inicio';
                case 'it': return 'Home';
                case 'pt': return 'Início';
                case 'nl': return 'Home';
                default: return 'Home';
            }
        }
        return null;
    }
}

class SlugNameResolver implements PageNameResolverInterface
{
    public function __construct(private Slugs $slugs, private array $languages)
    {
    }

    public function getName(string $page, Language $language): ?string
    {
        $slug = $this->slugs->getSlug($page, $language);
        if (null === $slug) {
            return $page;
        }
        $slug = explode('/', $slug);
        $last = array_pop($slug);
        return ucfirst(str_replace('-', ' ', $last));
    }
}
