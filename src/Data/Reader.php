<?php

declare(strict_types=1);
namespace Flipsite\Data;

use Flipsite\AbstractEnvironment;
use Flipsite\Exceptions\NoSiteFileFoundException;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Language;
use Flipsite\Utils\YamlExpander;
use Flipsite\Utils\Localizer;
use Flipsite\Utils\Plugins;
use Flipsite\Utils\DataHelper;

final class Reader
{
    /**
     * @var array<Language>
     */
    private array $languages = [];

    /**
     * @var array<string>
     */
    private array $languageCodes = [];

    /**
     * @var array<string,mixed>
     */
    private array $data;

    private Slugs $slugs;

    private ?PageNameResolver $pageNameResolver = null;

    private string $hash = '';

    private Localizer $localizer;

    private Plugins $plugins;

    public function __construct(private AbstractEnvironment $environment)
    {
        $this->plugins = $environment->getPlugins();
        $siteDir       = $this->environment->getSiteDir();
        if (file_exists($siteDir.'/site.yaml')) {
            $siteYaml = YamlExpander::parseFile($siteDir.'/site.yaml');
            $siteYaml = $this->plugins->run('beforeSiteLoad', $siteYaml);
            $this->loadSite($siteYaml);
        } else {
            throw new NoSiteFileFoundException($siteDir);
        }
    }

    public function loadSite(array $yaml)
    {
        $this->data          = $yaml;
        $this->expandPagesAndSlugs();
        foreach (explode(',', $this->data['languages']) as $language) {
            $this->languages[] = new Language($language);
        }
        $this->localizer = new Localizer($this->languages);
        $this->slugs     = new Slugs(
            array_keys($this->data['pages']),
            $this->data['slugs'] ?? null,
            $this->getDefaultLanguage(),
            $this->getLanguages()
        );
        $this->hash = md5(json_encode($this->data));
        if (isset($this->data['theme']['extend'])) {
            $this->data['theme']['components'] = ArrayHelper::merge($this->data['theme']['components'], $this->data['theme']['extend']);
            unset($this->data['theme']['extend']);
        }
    }

    public function getHash(int $length = 6): string
    {
        return substr($this->hash, 0, $length);
    }

    public function isOnline(): bool
    {
        return true;
    }

    public function get(string $path, ?Language $language = null)
    {
        $data = ArrayHelper::getDot(explode('.', $path), $this->data);
        if (null === $data || !is_array($data) || null === $language) {
            return $data;
        }
        return $this->localizer->localize($data, $language);
    }

    /**
     * @return array<Language>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getDefaultLanguage(): Language
    {
        return $this->languages[0];
    }

    public function getLocalizer(): Localizer
    {
        return $this->localizer;
    }

    public function getSlugs(): Slugs
    {
        return $this->slugs;
    }

    public function getRedirects(): ?array
    {
        return $this->data['redirects'] ?? [];
    }

    public function getPageName(string $page, ?Language $language = null, array $exclude = []): string
    {
        $language ??= $this->getDefaultLanguage();
        if (is_null($this->pageNameResolver)) {
            $this->pageNameResolver = new PageNameResolver(
                $this->data['meta'] ?? [],
                $this->getSlugs()
            );
        }
        return $this->pageNameResolver->getName($page, $language, $exclude);
    }

    public function getSections(string $page, ?Language $language = null): array
    {
        $language ??= $this->getDefaultLanguage();
        if ('offline' === $page) {
            return $this->localizer->localize($this->data['offline'] ?? [['text' => 'offline']], $language) ?? [];
        }
        $before = $this->data['before'] ?? [];
        if (ArrayHelper::isAssociative($before)) {
            $before = [$before];
        }
        $after = $this->data['after'] ?? [];
        if (ArrayHelper::isAssociative($after)) {
            $after = [$after];
        }
        $all = $this->data['pages'][$page] ?? [];
        if (ArrayHelper::isAssociative($all)) {
            $all = [$all];
        }
        foreach ($before as &$b) {
            $b['_before'] = true;
        }
        foreach ($after as &$a) {
            $a['_after'] = true;
        }
        $all      = array_merge($before, $all, $after);
        $sections = [];
        foreach ($all as $section) {
            $type = $section['type'] ?? 'default';
            if ($this->hideSection($section, $page, $language)) {
                continue;
            }
            $sections[] = $section;
        }
        foreach ($sections as $i => &$section) {
            $parentStyle = [];
            foreach ($section as $type => $value) {
                if ($parentStyle) {
                    continue;
                }
                $tmp    = explode(':', $type);
                $styles = [];
                while (count($tmp)) {
                    $t           = implode(':', $tmp);
                    $parentStyle = $this->get('theme.components.'.$t.'.section');
                    if (is_array($parentStyle)) {
                        $styles[$t] = $parentStyle;
                    }
                    array_pop($tmp);
                }
                $parentStyle = ArrayHelper::merge(...array_reverse($styles));
            }
            if (count($parentStyle)) {
                $section['parentStyle'] = $parentStyle;
                if (!isset($section['parentStyle']['type'])) {
                    $section['parentStyle']['type'] = 'group';
                }
            }
        }
        return $this->localizer->localize($sections ?? [], $language) ?? [];
    }

    public function getMeta(string $page, Language $language): ?array
    {
        $meta = [
            'title'       => $this->get('name'),
            'description' => $this->get('description', $language),
            'share'       => $this->get('share') ?? null
        ];

        if ($language != $this->languages[0]) {
            $meta['title'] .= ' ('.strtoupper((string)$language).')';
        }

        $titleSet = false;
        if (isset($this->data['meta'][$page])) {
            $pageMeta = $this->get('meta.'.$page, $language);
            if (isset($pageMeta['title'])) {
                $meta['title'] = $pageMeta['title'].' - '.$meta['title'];
                $titleSet      = true;
            }
            unset($pageMeta['name'],$pageMeta['title']);
            $meta = ArrayHelper::merge($meta, $pageMeta);
        }
        if ('home' !== $page && !$titleSet) {
            $p     = explode('/', $page);
            $title = [];
            while (count($p) > 0) {
                $page  = implode('/', $p);
                $pages = array_keys($this->data['pages']);
                if (in_array($page, $pages)) {
                    $name    = $this->getPageName($page, $language);
                    $title[] = $name;
                }
                array_pop($p);
            }
            $title[]       = $meta['title'];
            $meta['title'] = implode(' - ', $title);
        }

        return $meta;
    }

    public function getComponentFactories(): array
    {
        $factories = [];
        $import    = $this->data['theme']['import'] ?? [];
        if (!is_array($import)) {
            $import = explode(',', str_replace(' ', '', $import));
        }
        foreach ($import ?? [] as $factory) {
            $parts   = explode('/', $factory);
            $vendor  = str_replace(' ', '', ucwords(str_replace('-', ' ', $parts[0])));
            $package = str_replace(' ', '', ucwords(str_replace('-', ' ', $parts[1])));
            $class   = $vendor.'\\'.$package.'\\ComponentFactory';
            if (class_exists($class)) {
                $factories[] = $class;
            }
        }
        return $factories;
    }

    public function getHiddenPages(): array
    {
        $hidden = ['404'];
        foreach ($this->data['meta'] ?? [] as $page => $meta) {
            if ($meta['hidden'] ?? false) {
                $hidden[] = $page;
            }
        }
        return $hidden;
    }

    private function hideSection(array $section, string $page, Language $language): bool
    {
        if ($section['hidden'] ?? false) {
            return true;
        }
        if (isset($section['options']['visible']['languages'])) {
            $val       = $section['options']['visible']['languages'];
            $languages = is_string($val) ? explode(',', $val) : $val;
            if (!in_array((string) $language, $languages)) {
                return true;
            }
        }
        if (isset($section['options']['visible']['pages'])) {
            $pages = $section['options']['visible']['pages'];
            if (is_string($pages)) {
                $pages = explode(',', $pages);
            }
            foreach ($pages as $visiblePage) {
                if ($visiblePage === $page) {
                    return false;
                }
                if (str_ends_with($visiblePage, '*') && str_starts_with($page, str_replace('*', '', $visiblePage))) {
                    return false;
                }
            }
            return true;
        }
        if (isset($section['options']['hidden']['pages'])) {
            $pages = $section['options']['hidden']['pages'];
            if (is_string($pages)) {
                $pages = explode(',', $pages);
            }
            foreach ($pages as $hidePage) {
                if ($hidePage === $page) {
                    return true;
                }
                if (str_ends_with($hidePage, '*') && str_starts_with($page, str_replace('*', '', $hidePage))) {
                    return true;
                }
            }
        }
        return false;
    }

    private function expandPagesAndSlugs(): void
    {
        $pages    = $this->data['pages'] ?? [];
        $slugs    = $this->data['slugs'] ?? [];
        $meta     = $this->data['meta'] ?? [];

        $expandedPages = [];
        $expandedSlugs = [];
        $expandedMeta  = [];
        foreach ($pages as $page => $sections) {
            $path = new RawPath($page);
            if ($path->hasParams()) {
                foreach ($this->data['content'][$path->getContent()] as $dataItem) {
                    $expandedPage = $path->getPage($dataItem);

                    // Dont overwrite existing page
                    if (isset($expandedPages[$expandedPage])) {
                        continue 2;
                    }
                    // Add page. prefix to data item attributes
                    $pageDataItem = [];
                    foreach ($dataItem as $attr => $val) {
                        $pageDataItem['page.'.$attr] = $val;
                    }
                    $pageSections = $sections ?? [];
                    foreach ($pageSections as &$pageSection) {
                        $pageSection['_dataSource'] = $pageDataItem;
                    }
                    $expandedPages[$expandedPage] = $pageSections;
                    if (isset($meta[$page])) {
                        $expandedMeta[$expandedPage] = DataHelper::applyData($meta[$page], $pageDataItem);
                    }
                    // TODO localized slugs
                }
            } else {
                $expandedPages[$page] = $sections;
                if (isset($meta[$page])) {
                    $expandedMeta[$page] = $meta[$page];
                }
                if (isset($slugs[$page])) {
                    $expandedSlugs[$page] = $slugs[$page];
                }
            }
        }

        $this->data['pages'] = $expandedPages;
        $this->data['slugs'] = $expandedSlugs;
        $this->data['meta']  = $expandedMeta;
    }

    private function extendSlug(array $extendedSlugs, string $page, string|array $slugs, array $params, array $permutation): array
    {
        foreach ($params as $i => $param) {
            $page = str_replace($param, (string)$permutation[$i], $page);
            if (is_string($slugs)) {
                $slugs = str_replace($param, (string)$permutation[$i], $slugs);
            } else {
                foreach ($slugs as &$slug) {
                    $slug = str_replace($param, (string)$permutation[$i], $slug);
                }
            }
        }
        $extendedSlugs[$page] = $slugs;
        return $extendedSlugs;
    }

    private function getPermutations(int $level, int $max, array $data, array $parents = []): array
    {
        $permutations = [];
        foreach ($data as $key => $val) {
            if ($level < $max - 1 && is_array($val) && isset($val['data'])) {
                $permutations = array_merge($permutations, $this->getPermutations($level + 1, $max, $val['data'], array_merge($parents, [$key])));
            } else {
                $permutations[] = array_merge($parents, [$key]);
            }
        }
        return $permutations;
    }
}
