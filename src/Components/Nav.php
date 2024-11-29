<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Nav extends AbstractGroup
{
    protected string $tag  = 'nav';

    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => $data];
        }
        $repeat = [];
        if (!isset($data['_options']['pages'])) {
            $level           = 0;
            $parentPage      = $data['_options']['parentPage'] ?? null;
            if ($parentPage) {
                $level = substr_count($parentPage, '/') + 1;
            } elseif (isset($data['_repeat']) && str_starts_with($data['_repeat'], '_pages-')) {
                $level = intval(str_replace('_pages-', '', $data['_repeat']));
            }
            $repeat = $this->getPages($level, $parentPage);
        } else {
            $pages = ArrayHelper::decodeJsonOrCsv($data['_options']['pages']);
            foreach ($pages as $page) {
                $pageItemData = $this->getPageItemData($page);
                if ($pageItemData) {
                    $repeat[] = $pageItemData;
                } else {
                    $name     = null;
                    $fragment = null;
                    if (str_starts_with($page, '[') && str_ends_with($page, ')')) {
                        $tmp        = explode('](', substr($page, 1, -1));
                        $name       = $tmp[0];
                        $page       = $tmp[1];
                    }
                    $tmp      = explode('#', $page);
                    if (count($tmp) > 1) {
                        $page     = $tmp[0];
                        $fragment = $tmp[1];
                    }
                    $page = trim($page, '/');
                    if ($page) {
                        $pageItemData = $this->getPageItemData($page);
                        if ($pageItemData) {
                            $pageItemData['name'] = $name ?? $pageItemData['name'];
                            if ($fragment) {
                                $pageItemData['slug'] .= '#'.$fragment;
                            }

                            $repeat[] = $pageItemData;
                            continue;
                        }
                    } elseif ($fragment) {
                        $repeat[] = [
                            'slug' => '#'.$fragment,
                            'name' => $name ?? ucwords(str_replace('-', ' ', $fragment))
                        ];
                    }
                }
            }
        }

        if ($data['_options']['languages'] ?? false) {
            $languages            = $this->siteData->getLanguages();
            $hideActiveLanguage   = $data['_options']['hideActiveLanguage'] ?? false;
            if (count($languages) > 1) {
                $active = $this->path->getLanguage();
                foreach ($languages as $language) {
                    if (!$hideActiveLanguage || !$language->isSame($active)) {
                        $repeat[] = [
                            'slug'      => (string)$language,
                            'name'      => $language->getInLanguage(),
                        ];
                    }
                }
            }
        }

        if ($data['_options']['hideActive'] ?? false) {
            $active = $this->path->getPage();
            $repeat = array_filter($repeat, function ($item) use ($active) {
                return $item['slug'] !== $active;
            });
        }

        if (isset($data['_options']['sort'])) {
            $data['_options']['sortBy'] = 'name';
        }
        if (isset($data['_options']['filter'])) {
            $data['_options']['filterField'] = 'slug';
        }

        $data = $this->normalizeRepeat($data, $repeat);

        return $data;
    }

    private function getPages(int $level, ?string $parentPage = null): array
    {
        $pages      = [];
        $all        = $this->siteData->getSlugs()->getPages();
        $firstExact = false;
        if ($level === 0) {
            foreach ($all as $page) {
                if ('404' === $page) {
                    continue;
                }
                if (strpos($page, '/') === false && $pageItemData = $this->getPageItemData($page)) {
                    $pages[] = $pageItemData;
                }
            }
        } else {
            $parts           = explode('/', $parentPage ?? $this->path->getPage());
            $startsWith      = implode('/', array_splice($parts, 0, $level)).'/';
            foreach ($all as $page) {
                $count = substr_count((string)$page, '/');
                if (str_starts_with((string)$page, $startsWith) && $count >= $level - 1 && $count <= $level && $pageItemData = $this->getPageItemData($page)) {
                    $pages[] = $pageItemData;
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
        if (!$this->siteData->getSlugs()->isPage($page)) {
            $pattern = '/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/';
            preg_match($pattern, $page, $matches);
            if (count($matches) === 3) {
                return [
                    'slug'  => $matches[2],
                    'name'  => $matches[1]
                ];
            } else {
                return null;
            }
            return null;
        }
        $pageMeta = $this->siteData->getPageMeta($page, $this->path->getLanguage()) ?? [];

        if (isset($pageMeta['hidden']) && $pageMeta['hidden']) {
            return null;
        }

        if (isset($pageMeta['unpublished']) && $pageMeta['unpublished']) {
            return null;
        }
        return [
            'slug'  => $page,
            'name'  => $this->siteData->getPageName($page, $this->path->getLanguage()),
            ...$pageMeta
        ];
    }
}
