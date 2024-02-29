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
        $repeat = [];
        if (!isset($data['_options']['pages'])) {
            $level = 0;
            $parentPage = $data['_options']['parentPage'] ?? null;
            if (isset($data['_repeat']) && str_starts_with($data['_repeat'], '_pages-')) {
                $level = intval(str_replace('_pages-', '', $data['_repeat']));
            }
            $repeat = $this->getPages($level, $parentPage);
        } else {
            $pages = ArrayHelper::decodeJsonOrCsv($data['_options']['pages']);
            $repeat = [];
            foreach ($pages as $page) {
                $pageItemData = $this->getPageItemData($page);
                if ($pageItemData){
                    $repeat[] = $pageItemData;
                }
            }
        }

        if ($data['_options']['languages'] ?? false) {
            $languages = $this->siteData->getLanguages();
            if (count($languages) > 1) {
                $active = $this->path->getLanguage();
                foreach ($languages as $language) {
                    if (!$language->isSame($active)) {
                        $repeat[] = [
                            'slug'      => (string)$language,
                            'name'     => $language->getInLanguage(),
                        ];
                    }
                }
            }
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
                if (strpos($page,'/') === false && $pageItemData = $this->getPageItemData($page)) {
                    $pages[] = $pageItemData;
                }
            }
        } else {
            $parts           = explode('/', $parentPage ?? $this->path->getPage());
            $startsWith      = implode('/', array_splice($parts, 0, $level));
            foreach ($all as $page) {
                $count = substr_count((string)$page, '/');
                if (str_starts_with((string)$page, $startsWith) && $count >= $level - 1 && $count <= $level && $pageItemData = $this->getPageItemData($page)) {
                    $pages[] = $pageItemData;
                }
            }
        }
        return $pages;
    }

    private function getPageItemData(string $page) : ?array {
        if (!$this->siteData->getSlugs()->isPage($page)) {
            $pattern = '/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/';
            preg_match($pattern, $page, $matches);
            if (count($matches) === 3) {
                return [
                    'slug'  => $matches[2],
                    'name'  => $matches[1]
                ];
            } else return null;
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
