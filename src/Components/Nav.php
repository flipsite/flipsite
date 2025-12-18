<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

class Nav extends AbstractGroup
{
    protected string $tag  = 'nav';

    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    public function normalize(array $data): array
    {
        $activePage     = $this->path->getPage();
        $activeLanguage = $this->path->getLanguage();
        $options        = new NavOptions($data, $activePage);
        unset($data['_repeat'], $data['_options']['parentPage'], $data['_options']['includeParent'], $data['_options']['pages'], $data['_options']['languages'], $data['_options']['hideActiveLanguage'], $data['_options']['hideActive']);

        $pages  = $options->pages ?? $this->getPages($options->parentPage, $options->includeParent);

        $repeat = [];
        foreach ($pages as $page) {
            if ($pageItemData = $this->getPageItemData($page)) {
                $repeat[] = $pageItemData;
            }
        }

        // Languages
        if ($options->showLanguages) {
            $languages = $this->siteData->getLanguages();
            if (count($languages) > 1) {
                foreach ($languages as $language) {
                    if (!$options->hideActiveLanguage || !$language->isSame($activeLanguage)) {
                        $repeat[] = [
                            'slug'      => (string)$language,
                            'code'      => (string)$language,
                            'name'      => $language->getInLanguage(),
                        ];
                    }
                }
            }
        }

        if ($options->hideActive) {
            $repeat = array_filter($repeat, function ($item) use ($activePage) {
                return $item['slug'] !== $activePage;
            });
        }

        if (isset($data['_options']['sort'])) {
            $data['_options']['sortBy'] = 'name';
        }
        if (isset($data['_options']['filter'])) {
            $data['_options']['filterField'] = 'slug';
        }

        return $this->normalizeRepeat($data, $repeat);
    }

    private function getPages(?string $parentPage = null, bool $includeParent = false): array
    {
        $pages      = [];
        $all        = $this->siteData->getSlugs()->getPages();
        $firstExact = false;
        if (!$parentPage) {
            foreach ($all as $page) {
                if ('404' === $page) {
                    continue;
                }
                if (strpos($page, '/') === false) {
                    $pages[] = $page;
                }
            }
        } else {
            $level           = substr_count($parentPage, '/') + 1;
            $parts           = explode('/', $parentPage);
            $startsWith      = implode('/', array_splice($parts, 0, $level));
            foreach ($all as $page) {
                $count = substr_count((string)$page, '/');
                if ($includeParent && str_starts_with((string)$page, $startsWith)) {
                    $pages[] = $page;
                } elseif (str_starts_with((string)$page, $startsWith.'/') && $count >= $level - 1 && $count <= $level) {
                    $pages[] = $page;
                }
            }
        }
        return $pages;
    }

    private function getPageItemData(string $page) : ?array
    {
        if ('404' === $page) {
            return null;
        }

        $name     = null;
        $slug     = null;
        $fragment = null;
        $meta     = [];

        // Parse [Name](slug) syntax
        $pattern = '/\[([^\]]+)\]\(([^\)]+)\)/';
        preg_match($pattern, $page, $matches);
        if (count($matches) === 3) {
            $slug = $matches[2];
            $name = $matches[1];
        } else {
            $slug = $page;
        }

        if (strpos($slug, '#') !== false) {
            $tmp      = explode('#', $slug);
            $slug     = $tmp[0];
            $fragment = $tmp[1] ?? null;
        }

        // Parse section link #section syntax
        if ($this->siteData->getSlugs()->isPage($slug)) {
            $meta = $this->siteData->getPageMeta($slug, $this->path->getLanguage()) ?? [];
            $name ??= $this->siteData->getPageName($slug, $this->path->getLanguage()) ?? $name;
            if (isset($meta['hidden']) && $meta['hidden']) {
                return null;
            }
            if (isset($meta['unpublished']) && $meta['unpublished']) {
                return null;
            }
        }
        if (!$name) {
            $name = ucwords(str_replace(['-', '_'], ' ', basename($slug)));
        }
        if (!$name) {
            $name = ucwords(str_replace(['-', '_'], ' ', $fragment));
        }

        return [
            'slug'          => $slug . ($fragment ? '#'.$fragment : ''),
            'name'          => $name,
            ...$meta
        ];
    }
}

class NavOptions
{
    public readonly ?array $pages;
    public readonly ?string $parentPage;
    public readonly bool $includeParent;
    public readonly bool $hideActive;
    public readonly bool $showLanguages;
    public readonly bool $hideActiveLanguage;

    public function __construct(array $data, string $currentPage)
    {
        $pages       = $data['_options']['pages'] ?? null;
        if ($pages) {
            $this->pages      = ArrayHelper::decodeJsonOrCsv($pages);
            $this->parentPage = null;
        } elseif (isset($data['_options']['parentPage'])) {
            $this->pages      = null;
            $this->parentPage = trim($data['_options']['parentPage'], '/');
        } elseif (($data['_repeat'] ?? false)) {
            $this->pages     = null;
            $level           = intval(str_replace('_pages-', '', ($data['_repeat'] ?? '0'))) ?? 0;
            if ($level) {
                $parts            = explode('/', $currentPage);
                $this->parentPage = implode('/', array_splice($parts, 0, $level));
            } else {
                $this->parentPage = null;
            }
        } else {
            $this->pages      = null;
            $this->parentPage = null;
        }
        $this->includeParent           = !!($data['_options']['includeParent'] ?? false);
        $this->hideActive              = !!($data['_options']['hideActive'] ?? false);
        $this->showLanguages           = !!($data['_options']['languages'] ?? false);
        $this->hideActiveLanguage      = !!($data['_options']['hideActiveLanguage'] ?? false);
    }
}
