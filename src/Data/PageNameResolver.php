<?php

declare(strict_types=1);
namespace Flipsite\Data;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Language;

interface PageNameResolverInterface
{
    public function getName(string $page, Language $language) : ?string;
}

class PageNameResolver implements PageNameResolverInterface
{
    private array $resolvers = [];

    public function __construct(array $pages, Slugs $slugs)
    {
        $this->resolvers[] = new SectionNameResolver($pages);
        $this->resolvers[] = new HomeNameResolver();
        $this->resolvers[] = new SlugNameResolver($slugs);
    }

    public function getName(string $page, Language $language) : string
    {
        foreach ($this->resolvers as $resolver) {
            $name = $resolver->getName($page, $language);
            if ($name) {
                return $name;
            }
        }
        return '['.$page.']';
    }
}

class SectionNameResolver implements PageNameResolverInterface
{
    private array $names = [];

    public function __construct(array $pages)
    {
        foreach ($pages as $page => $sections) {
            if (null === $sections) {
                continue;
            }
            if (ArrayHelper::isAssociative($sections)) {
                $sections = [$sections];
            }
            foreach ($sections as $section) {
                if (isset($section['_name'])) {
                    $this->names[$page] = $section['_name'];
                }
            }
        }
    }

    public function getName(string $page, Language $language) : ?string
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
    public function getName(string $page, Language $language) : ?string
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

    public function getName(string $page, Language $language) : ?string
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
