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
        $this->pages = array_map('strval', $pages);
        if (!in_array('404', $this->pages)) {
            $this->pages[] = '404';
        }
        $this->languages = 0 === count($languages) ? [$default] : $languages;
        $this->slugs     = [];
        foreach ($this->pages as $page) {
            if (!$this->isAlreadySlug($page)) {
                $this->slugs[$page]                    = [];
                $this->slugs[$page][(string) $default] = self::HOME === $page ? '' : $page;
                if ('404' === $page) {
                    continue;
                }
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
    }

    private function isAlreadySlug(string $slug): bool
    {
        foreach ($this->slugs as $page => $slugs) {
            foreach ($slugs as $definedSlug) {
                if ($definedSlug === $slug) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getPages(): array
    {
        return $this->pages;
    }

    public function getAll(): array
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

    public function getSlug(string $page, Language $language = null, bool $fallback = true): ?string
    {
        if ($page === '404') {
            return '404';
        }
        if (count($this->languages) === 1) {
            return $page;
        }
        if (!isset($this->slugs[$page])) {
            return null;
        }
        $slug = $this->slugs[$page];
        if ($language !== null) {
            return $slug[(string) $language] ?? null;
        }
        $loc = ['_loc' => true];
        foreach ($slug as $lang => $localizedSlug) {
            if (!$fallback || !str_starts_with($localizedSlug, (string) $lang.'/')) {
                $loc[$lang] = $localizedSlug;
            }
        }
        return json_encode($loc);
    }

    public function isPage(string $page): bool
    {
        return in_array($page, $this->pages);
    }

    public function getPage(string $slug): ?string
    {
        if ($slug === 'home') {
            return 'home';
        }
        foreach ($this->slugs as $page => $slugs) {
            foreach ($slugs as $langauge => $localizedSlug) {
                if ($localizedSlug === $slug) {
                    return $page;
                }
            }
        }
        return null;
    }

    public function getPath(string $path, ?Language $language = null, string $active = 'home'): ?string
    {
        // Check if path is language, replace path with current page

        foreach ($this->slugs as $pageId => $slugs) {
            foreach ($slugs as $slugLanguage => $slug) {
                if ($slugLanguage === (string)$language && $slug === $path) {
                    return '/'.$path;
                }
            }
        }

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

    public function hasSubpages(string $page): bool
    {
        // Check if has direct subpages
        foreach ($this->pages as $page_) {
            if (str_starts_with($page_, $page.'/')) {
                $subpage = str_replace($page.'/', '', $page_);
                return strpos($subpage, '/') === false;
            }
        }
        return false;
    }

    private function convertPathToPage(string $path): ?string
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

    private function getPathLanguage(string $path): ?Language
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

    private function isMultilingual(): bool
    {
        return count($this->languages) > 1;
    }

    private function isBilingual(): bool
    {
        return 2 === count($this->languages);
    }
}
