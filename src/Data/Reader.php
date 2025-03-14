<?php

declare(strict_types=1);

namespace Flipsite\Data;

use Flipsite\Exceptions\NoSiteFileFoundException;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Language;
use Symfony\Component\Yaml\Yaml;
use Flipsite\Utils\Localizer;
use Flipsite\Utils\Localization;
use Flipsite\Utils\Plugins;
use Flipsite\Utils\DataHelper;
use Flipsite\Utils\CustomHtmlParser;
use Flipsite\Content\Collection;

class Reader implements SiteDataInterface
{
    use ComponentTypesTrait;
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

    private array $expandedPages = [];

    private Slugs $slugs;

    private ?PageNameResolver $pageNameResolver = null;

    private string $hash = '';

    private Localizer $localizer;

    private ?CustomHtmlParser $customParser = null;

    private ?\Adbar\Dot $componentsStyles = null;

    public function __construct(private string $siteDir, private ?Plugins $plugins = null, bool $expand = true)
    {
        if (file_exists($siteDir . '/site.yaml')) {
            $siteYaml  = Yaml::parseFile($siteDir . '/site.yaml');
            $themeYaml = Yaml::parseFile($siteDir . '/theme.yaml');
            $siteYaml  = $this->repairSite($siteYaml, $siteDir . '/site.yaml');
            $themeYaml = $this->repairTheme($themeYaml, $siteDir . '/theme.yaml');

            $siteYaml['theme'] = $themeYaml;
            if ($this->plugins) {
                $siteYaml = $this->plugins->run('beforeSiteLoad', $siteYaml);
            }
            $this->loadSite($siteYaml, $expand);
        } else {
            throw new NoSiteFileFoundException($siteDir);
        }
        if (file_exists($siteDir . '/custom.html')) {
            $customHtml         = file_get_contents($siteDir . '/custom.html');
            $this->customParser = new CustomHtmlParser($customHtml);
        }
    }

    public function getCollectionIds(): array
    {
        $collectionIds = array_keys($this->get('contentSchemas') ?? []);
        sort($collectionIds);
        return $collectionIds;
    }

    public function getCollection(string $collectionId, ?Language $language = null): ?Collection
    {
        $schema = $this->get('contentSchemas.' . $collectionId);
        if (!$schema) {
            return null;
        }
        return new Collection($collectionId, $schema, $this->get('content.' . $collectionId, $language) ?? []);
    }

    public function getModifiedTimestamp(): int
    {
        $res = \Flipsite\Utils\FileHelper::getMostRecentlyModifiedFile($this->siteDir);
        return $res['modificationTime'];
    }

    public function getCode(string $position, string $page, bool $fallback): ?string
    {
        if (!$this->customParser) {
            return null;
        }
        return $this->customParser->get($position, $page, $fallback);
    }

    public function getCompile(): ?array
    {
        return $this->get('compile');
    }

    public function getPublish(): ?array
    {
        return $this->get('publish');
    }

    private function loadSite(array $yaml, bool $expand)
    {
        $this->data = $yaml;
        foreach (explode(',', $this->data['languages']) as $language) {
            $this->languages[] = new Language($language);
        }
        if ($expand) {
            $this->expandPagesAndSlugs();
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
        if (null !== $language && $data) {
            return $this->localizer->localize($data, $language);
        }
        return $data;
    }

    public function getName(): string
    {
        return (string)$this->get('name');
    }

    public function getTitle(?Language $language = null): ?string
    {
        return $this->get('title', $language);
    }

    public function getDescription(?Language $language = null): ?string
    {
        return $this->get('description', $language);
    }

    public function getShare(): ?string
    {
        return $this->get('share');
    }

    public function getThemeColor(): ?string
    {
        return $this->get('themeColor');
    }

    public function getAppleAppId(): ?string
    {
        $appId = $this->get('appleAppId');
        return $appId ? (string)$appId : null;
    }

    public function getSocial(): array
    {
        return $this->get('social') ?? [];
    }

    public function getFavicon(): null|string|array
    {
        $favicon = $this->get('favicon') ?? null;
        if (is_array($favicon)) {
            return array_shift($favicon);
        }
        return $favicon;
    }

    public function getIntegrations(): ?array
    {
        return $this->get('integrations') ?? null;
    }

    public function getRedirects(): ?array
    {
        return $this->get('redirects') ?? null;
    }

    public function getColors(): array
    {
        $colors = $this->data['theme']['colors'];
        if (!isset($colors['gray'])) {
            $colors['gray'] = \Flipsite\Utils\ColorHelper::getGray($colors['primary']);
        }
        return $colors;
    }

    public function getGlobalVars(): array
    {
        return $this->data['globalVars'] ?? [];
    }

    public function getFonts(): array
    {
        return $this->data['theme']['fonts'] ?? [];
    }

    public function setFonts(array $fonts): void
    {
        $this->data['theme']['fonts'] = $fonts;
    }

    public function getThemeSettings(): array
    {
        $style    = $this->data['theme']['components']['html'] ?? [];
        $settings = [];

        if (isset($style['textScale'])) {
            $value                 = str_replace('text-scale-', '', $style['textScale']);
            $settings['textScale'] = floatval($value) / 100.0;
        } else {
            $settings['textScale'] = 1.0;
        }

        if (isset($style['spacingScale'])) {
            $value                    = str_replace('spacing-scale-', '', $style['spacingScale']);
            $settings['spacingScale'] = floatval($value) / 100.0;
        } else {
            $settings['spacingScale'] = 1.0;
        }

        if (isset($style['borderRadiusScale'])) {
            $value                         = str_replace('rounded-scale-', '', $style['borderRadiusScale']);
            $settings['borderRadiusScale'] = floatval($value) / 100.0;
        } else {
            $settings['borderRadiusScale'] = 1.0;
        }

        return $settings;
    }

    public function getAppearance(?string $page = null): string
    {
        return $this->data['theme']['components']['html']['appearance'] ?? 'light';
    }

    public function getHtmlStyle(?string $page = null): array
    {
        $style = $this->data['theme']['components']['html'] ?? [];
        unset($style['appearance'], $style['textScale'], $style['spacingScale'], $style['borderRadiusScale']);

        return $style;
    }

    public function getBodyStyle(?string $page = null): array
    {
        $style = $this->data['theme']['components']['body'] ?? [];
        unset($style['bgColor'], $style['dark']['bgColor']);
        $settings = $this->getThemeSettings();
        if ($settings['textScale'] !== 1.0) {
            $style['textSize'] = 'text-['.intval($settings['textScale'] * 100).'%]';
        }
        return $style;
    }

    public function getComponentStyle(int|string $componentId): array
    {
        if (!$this->componentsStyles) {
            $this->componentsStylesDot = new \Adbar\Dot($this->data['theme']['components']);
        }
        $all   = $this->componentsStylesDot->get($componentId) ?? [];
        $style = [];
        foreach ($all as $attr => $value) {
            if (!$this->isComponent($this->getType($attr))) {
                $style[$attr] = $value;
            }
        }
        return $style;
    }

    public function getInheritedStyle(int|string $componentId): array
    {
        $parts     = explode('.', $componentId);
        $sectionId = $parts[0];
        if (isset($this->data['theme']['components'][$sectionId]['inherit'])) {
            $inheritId = $this->data['theme']['components'][$sectionId]['inherit'];
            while ($inheritId) {
                if (isset($this->data['theme']['components'][$inheritId]['inherit'])) {
                    $inheritId = $this->data['theme']['components'][$inheritId]['inherit'];
                } else {
                    $parts[0] = $inheritId;
                    return $this->getComponentStyle(implode('.', $parts));
                }
            }
        }

        return [];
    }
    public function getSharedStyle(int|string $sharedStyleId): array
    {
        return $this->data['theme']['styles'][$sharedStyleId] ?? [];
    }

    private function getType(string $componentId): string
    {
        if (str_contains($componentId, '.') || str_contains($componentId, ':')) {
            $tmp  = explode('.', $componentId);
            $tmp2 = explode(':', $tmp[0]);
            return $tmp2[0];
        }
        return $componentId;
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

    public function getExpanded(string $page): ?array
    {
        return $this->expandedPages[$page] ?? null;
    }

    public function getPageName(string $page, ?Language $language = null, array $exclude = []): string
    {
        $language ??= $this->getDefaultLanguage();
        if (is_null($this->pageNameResolver)) {
            $this->pageNameResolver = new PageNameResolver(
                $this->getLanguages(),
                $this->data['meta'] ?? [],
                $this->getSlugs()
            );
        }
        return $this->pageNameResolver->getName($page, $language, $exclude);
    }

    public function getSections(string $page, ?Language $language = null): array
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
        $sections = $this->localizer->localize($sections ?? [], $language) ?? [];

        $sections = $this->normalize($sections);

        $componentDataList = [];

        foreach ($sections as $section) {
            $sectionId           = is_string($section['_style']) ? $section['_style'] : (string)time();
            $before              = $section['_before'] ?? false;
            $after               = $section['_after'] ?? false;
            $section['_style']   = $this->data['theme']['components'][$sectionId] ?? [];
            unset($section['_before'], $section['_after']);
            $componentData       = new YamlComponentData(null, $sectionId, 'container', $section);
            if ($before) {
                $componentData->setMetaValue('before', true);
            }
            if ($after) {
                $componentData->setMetaValue('after', true);
            }
            $componentDataList[] = $componentData;
        }

        return $componentDataList;
    }

    public function getComponent(int|string $componentId): ?AbstractComponentData
    {
        $parts     = explode('.', $componentId);
        $sectionId = array_shift($parts);

        $sections = [];
        foreach ($this->data['pages'] as $pageSections) {
            $sections = array_merge($sections, $pageSections);
        }
        $sections = array_merge($sections, $this->data['before'] ?? []);
        $sections = array_merge($sections, $this->data['after'] ?? []);

        foreach ($sections as $section) {
            if ($section['_style'] === $sectionId) {
                if (count($parts) === 0) {
                    $section['_style']   = $this->data['theme']['components'][$sectionId] ?? [];
                    return new YamlComponentData(null, $componentId, 'container', $section);
                } else {
                    $componentPathInSection = implode('.', $parts);
                    $dot                    = new \Adbar\Dot($section);
                    $data                   = $dot->get($componentPathInSection);
                    if (!is_array($data)) {
                        $data = ['value' => $data];
                    }
                    $last           = array_pop($parts);
                    $parts          = array_merge([$sectionId], $parts);
                    $path           = [];
                    while (count($parts)) {
                        $path[] = implode('.', $parts);
                        array_pop($parts);
                    }
                    $path = array_reverse($path);

                    $data['_style'] = $this->getComponentStyle($componentId);
                    return new YamlComponentData($path, $componentId, $this->getType($last), $data ?? ['value' => '']);
                }
            }
        }
        return null;
    }

    public function findComponent(int|string $rootComponentId, string $attribute, string|bool $value): ?AbstractComponentData
    {
        $componentData = $this->getComponent($rootComponentId);
        if (!$componentData) {
            return null;
        }

        $dot  = new \Adbar\Dot($componentData->getData());
        $flat = $dot->flatten();
        if (isset($flat[$attribute]) && $flat[$attribute] === $value) {
            return $componentData;
        }
        foreach ($componentData->getChildren() as $childComponent) {
            $result = $this->findComponent($childComponent->getId(), $attribute, $value);
            if ($result) {
                return $result;
            }
        }
        return null;
    }

    public function getMeta(string $page, ?Language $language = null): ?array
    {
        $pageMeta    = $this->getPageMeta($page, $language) ?? [];

        $description = $pageMeta['description'] ?? $this->get('description', $language);
        $canonical   = $pageMeta['canonical'] ?? null;
        $share       = $pageMeta['share'] ?? $this->get('share') ?? null;
        $icon        = $pageMeta['icon'] ?? null;

        $title = '';
        // Build title
        if ('home' === $page) {
            $title = $pageMeta['title'] ?? $this->get('title', $language) ?? $this->get('name');
        } elseif (isset($pageMeta['title'])) {
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
            $title[] = $this->get('title', $language) ?? $this->get('name');
            $title   = implode(' - ', $title);
        }

        if ($language && !$language->isSame($this->getDefaultLanguage())) {
            $title .= ' ('.$language->getInLanguage().')';
        }
        $meta = [
            'title'       => $title,
            'description' => $description,
            'share'       => $share,
            'icon'        => $icon
        ];
        if ($canonical) {
            $meta['canonical'] = $canonical;
        }

        return $meta;
    }

    public function getPageMeta(string $page, ?Language $language = null): ?array
    {
        return $this->get('meta.' . $page, $language);
    }

    public function getHiddenPages(): array
    {
        $hidden = ['404'];
        foreach ($this->data['meta'] ?? [] as $page => $meta) {
            if ($meta['hidden'] ?? false) {
                $hidden[] = $page;
            }
            if ($meta['unpublished'] ?? false) {
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

        $languages    = $this->getLanguages();
        $mainLanguage = array_shift($languages); //all languages except main one

        foreach ($pages as $page => $sections) {
            $page = (string)$page;
            if (substr_count($page, ':slug') && isset($meta[$page]['content'])) {
                $this->expandedPages[$page] = [(string)$mainLanguage => $page];
                $collection                 = $this->getCollection($meta[$page]['content']);
                if (!$collection) {
                    continue;
                }

                $slugField = $collection->getSlugField();
                $items     = $collection->getItemsArray(true);
                if ($slugField && $items) {
                    foreach ($items as $dataItem) {
                        if (!isset($dataItem[$slugField]) || !$dataItem[$slugField]) {
                            continue;
                        }

                        $loc          = new Localization($this->languages, $dataItem[$slugField]);
                        $expandedPage = str_replace(':slug', $loc->getValue() ?? '', $page);

                        // Dont overwrite existing page
                        if (isset($expandedPages[$expandedPage])) {
                            continue;
                        }
                        // Add page. prefix to data item attributes
                        $pageDataItem = [];
                        foreach ($dataItem as $attr => $val) {
                            $pageDataItem['page.' . $attr] = $val;
                        }
                        $pageSections = $sections ?? [];
                        foreach ($pageSections as &$pageSection) {
                            $pageSection['_pageDataSource'] = $pageDataItem;
                        }
                        $expandedPages[$expandedPage] = $pageSections;
                        if (isset($meta[$page])) {
                            $expandedMeta[$expandedPage] = DataHelper::applyData($meta[$page], $pageDataItem);
                        }

                        foreach ($languages as $language) {
                            $localizedSlug = $slugs[$page][(string)$language] ?? (string)$language.'/'.$page;
                            if ($localizedSlug) {
                                $this->expandedPages[$page][(string)$language]   = $localizedSlug;
                                $expandedLocalizedPage                           = str_replace(':slug', $loc->getValue($language) ?? '', $localizedSlug);
                                $expandedPages[$expandedLocalizedPage]           = $expandedPages[$expandedPage];
                                $expandedMeta[$expandedLocalizedPage]            = $expandedMeta[$expandedPage];
                                $expandedSlugs[$expandedPage][(string)$language] = $expandedLocalizedPage;
                            }
                        }
                    }
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

    private function normalize(array $data): array
    {
        $normalized = [];
        foreach ($data as $attr => $value) {
            if ($attr === '_dataSourceList') {
                $attr = '_repeat';
                if (is_string($value) && str_starts_with($value, '${content.')) {
                    $value = str_replace('${content.', '', $value);
                    $value = substr($value, 0, strlen($value) - 1);
                }
            }
            $normalized[$attr] = is_array($value) ? $this->normalize($value) : $value;
        }
        return $normalized;
    }

    private function repairSite(array $site, string $sourcePath): array
    {
        if (($site['_version'] ?? 0) < 1) {
            $site = ArrayHelper::renameKey($site, '_dataSourceList', '_repeat', true);
            $site = ArrayHelper::applyStringCallback($site, function ($value, $attribute): string {
                if ('_repeat' === $attribute) {
                    if (str_starts_with($value, '${content.')) {
                        $value = str_replace('${content.', '', $value);
                        $value = substr($value, 0, strlen($value) - 1);
                        error_log('Updated _repeat '.$value);
                    }
                    return $value;
                }
                if ('_dataSource' === $attribute) {
                    if (str_starts_with($value, '${content.')) {
                        $value = str_replace('${content.', '', $value);
                        $value = substr($value, 0, strlen($value) - 1);
                    }
                    $tmp    = explode('.', $value);
                    $tmp[1] = intval($tmp[1]) + 1;
                    $value  = implode('.', $tmp);
                    error_log('Updated datasource to '.$value);
                    return $value;
                }
                return $value;
            });
            if (isset($site['content'])) {
                foreach ($site['content'] as $collectionId => &$items) {
                    foreach ($items as $index => &$item) {
                        if (!isset($item['_id'])) {
                            $item['_id'] = $index + 1;
                            error_log($collectionId . ' ' . $index . ' => ID ' . $item['_id']);
                        }
                    }
                }
            }
            $site['_version'] = 1;
            error_log('Repaired site.yaml');
            $this->dumpYaml($site, $sourcePath);
        }

        return $site;
    }

    private function repairTheme(array $theme, string $sourcePath): array
    {
        if (($theme['_version'] ?? 0) < 1) {
            unset($theme['colors']['light'], $theme['colors']['dark']);

            $theme['components']['html'] ??= [];
            $theme['components']['html']['heading'] ??= $theme['components']['heading'];
            $bodyFontWeght = $theme['components']['body']['fontWeight'] ?? null;
            unset($theme['components']['body']);
            $theme['components']['body']['textColor']         = 'text-gray-l11';
            $theme['components']['body']['dark']['textColor'] = 'text-gray-d11';
            if ($bodyFontWeght) {
                $theme['components']['body']['font-weight'] = $bodyFontWeght;
            }
            unset($theme['components']['heading'], $theme['components']['button'], $theme['components']['container'], $theme['components']['form'], $theme['components']['icon'], $theme['components']['input'], $theme['components']['link'], $theme['components']['logo'], $theme['components']['question'], $theme['components']['label'], $theme['components']['nav'], $theme['components']['paragraph'], $theme['components']['social'], $theme['components']['svg'], $theme['components']['tagline'], $theme['components']['textarea'], $theme['components']['toggle']);

            $theme['_version'] = 1;
            error_log('Repaired theme.yaml');
            $this->dumpYaml($theme, $sourcePath);
        }
        return $theme;
    }

    private function dumpYaml(array $yaml, string $path)
    {
        $yaml = Yaml::dump($yaml, 16, 2);
        $yaml = str_replace("''", '', $yaml);
        file_put_contents($path, $yaml);
    }
}
