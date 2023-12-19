<?php

declare(strict_types=1);
namespace Flipsite\Data;

use Flipsite\Exceptions\NoSiteFileFoundException;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Language;
use Symfony\Component\Yaml\Yaml;
use Flipsite\Utils\Localizer;
use Flipsite\Utils\Plugins;
use Flipsite\Utils\DataHelper;
use Flipsite\Utils\CustomHtmlParser;
use Flipsite\Content\Collection;

final class Reader implements SiteDataInterface
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

    private ?CustomHtmlParser $customParser = null;

    public function __construct(private string $siteDir, private ?Plugins $plugins = null, bool $expand = true)
    {
        if (file_exists($siteDir.'/site.yaml')) {
            $siteYaml          = Yaml::parseFile($siteDir.'/site.yaml');
            $themeYaml         = Yaml::parseFile($siteDir.'/theme.yaml');
            $siteYaml['theme'] = $themeYaml;
            if ($this->plugins) {
                $siteYaml = $this->plugins->run('beforeSiteLoad', $siteYaml);
            }
            $this->loadSite($siteYaml, $expand);
        } else {
            throw new NoSiteFileFoundException($siteDir);
        }
        if (file_exists($siteDir.'/custom.html')) {
            $customHtml         = file_get_contents($siteDir.'/custom.html');
            $this->customParser = new CustomHtmlParser($customHtml);
        }
    }

    public function getCollectionIds(): array
    {
        $collectionIds = array_keys($this->get('contentSchemas') ?? []);
        sort($collectionIds);
        return $collectionIds;
    }

    public function getCollection(string $collectionId): ?Collection
    {
        $schema = $this->get('contentSchemas.'.$collectionId);
        if (!$schema) {
            return null;
        }
        return new Collection($collectionId, $schema, $this->get('content.'.$collectionId));
    }

    public function getModifiedTimestamp() : int
    {
        return filemtime($this->siteDir);
    }

    public function getCode(string $position, string $page, bool $fallback) : ?string
    {
        if (!$this->customParser) {
            return null;
        }
        return $this->customParser->get($position, $page, $fallback);
    }

    public function getCompile(): ?array {
        return $this->get('compile');
    }

    public function getPublish(): ?array {
        return $this->get('publish');
    }

    private function loadSite(array $yaml, bool $expand)
    {
        $this->data          = $yaml;
        if ($expand) {
            $this->expandPagesAndSlugs();
        }
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

    public function get(string $path, ?Language $language = null)
    {
        $data = ArrayHelper::getDot(explode('.', $path), $this->data);
        if (null === $data || !is_array($data) || null === $language) {
            return $data;
        }
        return $this->localizer->localize($data, $language);
    }

    public function getName() : string
    {
        return $this->get('name');
    }

    public function getTitle(Language $language) : ?string
    {
        return $this->get('title', $language);
    }

    public function getDescription(Language $language) : ?string
    {
        return $this->get('description', $language);
    }

    public function getShare() : ?string
    {
        return $this->get('share');
    }

    public function getSocial() : array
    {
        return $this->get('social');
    }

    public function getFavicon() : null|string|array
    {
        $favicon = $this->get('favicon') ?? null;
        if (is_array($favicon)) {
            return array_shift($favicon);
        }
        return $favicon;
    }

    public function getIntegrations() : ?array
    {
        return $this->get('integrations') ?? null;
    }

    public function getColors() : array
    {
        return $this->data['theme']['colors'] ?? [];
    }

    public function getFonts() : array
    {
        return $this->data['theme']['fonts'] ?? [];
    }

    public function getHtmlStyle() : array
    {
        return $this->data['theme']['components']['html'] ?? [];
    }

    public function getBodyStyle(string $page) : array
    {
        return $this->data['theme']['components']['body'] ?? [];
    }

    public function getComponentStyle(string $component) : array
    {
        return $this->data['theme']['components'][$component] ?? [];
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

    public function getSections(string $page, Language $language): array
    {
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
        $sections = array_merge($before, $all, $after);

        return $this->localizer->localize($sections ?? [], $language) ?? [];
    }

    public function getMeta(string $page, Language $language): ?array
    {
        $pageMeta    = $this->getPageMeta($page, $language) ?? [];
        $description = $pageMeta['description'] ?? $this->get('description', $language);
        $share       = $pageMeta['share'] ?? $this->get('share') ?? null;
        $icon        = $pageMeta['icon'] ?? null;

        if ('home' === $page) {
            $title = $pageMeta['title'] ?? $this->get('title', $language) ?? $this->get('name', $language);
        } else {
            $baseTitle = $this->get('title', $language);
            if (isset($pageMeta['title'])) {
                $title = $pageMeta['title'];
            } else {
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
                $title = implode(' - ', $title);
            }
            if ($baseTitle) {
                $title .= ' - '.$baseTitle;
            }
        }
        return [
            'title'       => $title,
            'description' => $description,
            'share'       => $share,
            'icon'        => $icon
        ];
    }

    public function getPageMeta(string $page, Language $language) : ?array
    {
        return $this->get('meta.'.$page, $language);
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

    private function expandPagesAndSlugs(): void
    {
        $pages    = $this->data['pages'] ?? [];
        $slugs    = $this->data['slugs'] ?? [];
        $meta     = $this->data['meta'] ?? [];

        $expandedPages = [];
        $expandedSlugs = [];
        $expandedMeta  = [];
        foreach ($pages as $page => $sections) {
            if (substr_count($page, ':slug') && isset($meta[$page]['content'])) {
                $category = $meta[$page]['content'];
                $schema   = $this->data['contentSchemas'][$category];
                $items    = $this->data['content'][$category] ?? [];
                if (isset($schema['published']) && 'boolean' === $schema['published']['type']) {
                    $published = array_filter($items, function ($item) {
                        return $item['published'] ?? false;
                    });
                    $items = count($published) ? $published : [$items[0]];
                }

                foreach ($items as $dataItem) {
                    if (!isset($dataItem['slug'])) {
                        continue;
                    }
                    $expandedPage = str_replace(':slug', $dataItem['slug'], $page);

                    // Dont overwrite existing page
                    if (isset($expandedPages[$expandedPage])) {
                        continue;
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
        //print_r(array_keys($expandedPages,true));
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
}
