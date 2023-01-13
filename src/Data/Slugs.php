<?php

declare(strict_types=1);
namespace Flipsite\Data;

use Flipsite\Utils\Language;

final class Slugs
{
    private const HOME = 'home';

    private array $pages;

    private array $slugs;
    /**
     * @var array<Language>
     */
    private array $languages;

    public function __construct(array $pages, ?array $slugs, Language $default, array $languages = [])
    {
        $this->pages     = $pages;
        $this->languages = 0 === count($languages) ? [$default] : $languages;
        $this->slugs     = [];
        foreach ($this->pages as $page) {
            $this->slugs[$page]                    = [];
            $this->slugs[$page][(string) $default] = self::HOME === $page ? '' : $page;
            foreach ($this->languages as $language) {
                if ((string) $language === (string) $default) {
                    continue;
                }
                if (isset($slugs[$page])) {
                    $slug = $slugs[$page];
                    // slug is an object
                    if (is_array($slug)) {
                        $this->slugs[$page][(string) $language] = $slug[(string) $language] ?? trim($language.'/'.$page, '/');
                    }
                } else {
                    $this->slugs[$page][(string) $language] = self::HOME === $page ? (string) $language : $language.'/'.$page;
                }
            }
        }
    }

    public function getPages() : array
    {
        return $this->pages;
    }

    public function getAll() : array
    {
        $all = [];
        foreach ($this->languages as $language) {
            foreach ($this->slugs as $slug => $pages) {
                if (isset($pages[(string) $language])) {
                    $page       = $pages[(string) $language];
                    $all[$page] = $pages;
                }
            }
        }
        return $all;
    }

    public function getSlug(string $page, Language $language) : ?string
    {
        if (!isset($this->slugs[$page])) {
            return null;
        }
        return (string)$this->slugs[$page][(string) $language] ?? null;
    }

    public function isPage(string $page) : bool
    {
        return in_array($page, $this->pages);
    }

    public function getPage(string $slug) : ?string
    {
        foreach ($this->slugs as $page => $slugs) {
            foreach ($slugs as $langauge => $localizedSlug) {
                if ($localizedSlug === $slug) {
                    return $page;
                }
            }
        }
        return null;
    }

    public function getSimilarPage(string $slug, float $neededSimilarity = 85.0) : ?string
    {
        foreach ($this->slugs as $page => $slugs) {
            foreach ($slugs as $langauge => $localizedSlug) {
                similar_text((string)$localizedSlug, (string)$slug, $percent);
                if ($percent >= $neededSimilarity) {
                    return $page;
                }
            }
        }
        return null;
    }

    public function getLanguage(string $slug) : ?Language
    {
        foreach ($this->slugs as $page => $slugs) {
            foreach ($slugs as $langauge => $localizedSlug) {
                if ($localizedSlug === $slug) {
                    return new Language($langauge);
                }
            }
        }
        return null;
    }

    public function getPath(string $path, ?Language $language = null, string $active = 'home') : ?string
    {
        $pathLanguage = $this->getPathLanguage($path);
        if ($pathLanguage) {
            $language = $pathLanguage;
            $path     = $active;
        }
        $page = $this->convertPathToPage($path);
        if (null === $page) {
            return null;
        }

        if (!$this->isMultilingual()) {
            return self::HOME === $page ? '/' : '/'.$page;
        }
        return '/'.$this->slugs[$page][(string) $language] ?? null;
    }

    private function convertPathToPage(string $path) : ?string
    {
        $page = trim($path, '/');

        // Empty path is HOME
        if ('' === $page) {
            $page = self::HOME;
        }

        // If the path is only a language code, it refers to the home page in that language
        foreach ($this->languages as $language) {
            if ($page === (string) $language) {
                $page = self::HOME;
            }
        }
        // Return page if it's defined
        if (in_array($page, $this->pages)) {
            return $page;
        }

        // Check if page is a localized slug
        if ($this->isMultilingual()) {
            foreach ($this->slugs as $slugPage => $slug) {
                foreach ($slug as $language => $localizedSlug) {
                    if ($localizedSlug === $page) {
                        return $slugPage;
                    }
                }
            }
        }

        return null;
    }

    private function getPathLanguage(string $path) : ?Language
    {
        if ($this->isMultilingual()) {
            foreach ($this->languages as $language) {
                if ($language->isSame($path)) {
                    return $language;
                }
            }
        }
        return null;
    }

    private function isMultilingual() : bool
    {
        return count($this->languages) > 1;
    }

    private function isBilingual() : bool
    {
        return 2 === count($this->languages);
    }
}
