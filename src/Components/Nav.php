<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Nav extends AbstractGroup
{
    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    public function normalize(string|int|bool|array $data): array
    {
        $repeat = [];
        if (!isset($data['_options']['pages'])) {
            if (str_starts_with($data['_repeat'], '_pages-')) {
                $level = intval(str_replace('_pages-', '', $data['_repeat']));
            }
            $parentPage = $data['_options']['parentPage'] ?? null;
            $repeat = $this->getPages($level, $parentPage);
            $data = $this->normalizeRepeat($data, $repeat);
        }
        return $data;
    }

    private function getPages(int $level, ?string $parentPage = null): array
    {
        $pages      = [];
        $all        = $this->siteData->getSlugs()->getPages();
        $firstExact = false;
        if ($level === 0) {
            $pages = array_filter($all, function ($value) {
                return mb_strpos((string)$value, '/') === false;
            });
        } else {
            $parts           = explode('/', $parentPage ?? $this->path->getPage());
            $startsWith      = implode('/', array_splice($parts, 0, $level));

            foreach ($all as $page) {
                $count = substr_count((string)$page, '/');
                if (str_starts_with((string)$page, $startsWith) && $count >= $level - 1 && $count <= $level) {
                    $pages[] = $page;
                }
            }
        }

        $items = [];
        foreach ($pages as $page) {
            $pageMeta        = $this->siteData->getMeta((string)$page, $this->path->getLanguage()) ?? [];
            $item            = [
                'slug'  => $page,
                'name'  => $this->siteData->getPageName((string)$page, $this->path->getLanguage()),
                ...$pageMeta
            ];
            $items[] = $item;
        }
        return $items;
    }
}
