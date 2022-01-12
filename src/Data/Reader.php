<?php

declare(strict_types=1);
namespace Flipsite\Data;

use Flipsite\Enviroment;
use Flipsite\Exceptions\NoSiteFileFoundException;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Language;
use Flipsite\Utils\YamlExpander;

final class Reader
{
    private Enviroment $enviroment;
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

    private ?array $pageNames = null;

    public function __construct(Enviroment $enviroment)
    {
        $this->enviroment = $enviroment;
        $siteDir          = $this->enviroment->getSiteDir();
        if (file_exists($siteDir.'/site.yaml')) {
            $this->loadSite(YamlExpander::parseFile($siteDir.'/site.yaml'));
        } else {
            throw new NoSiteFileFoundException($siteDir);
        }
    }

    public function loadSite(array $yaml)
    {
        $this->data          = $yaml;
        $this->data['pages'] = $this->expandPages($this->data['pages']);
        $this->parseLanguages();
        $this->slugs = new Slugs(
            array_keys($this->data['pages'] ?? []),
            $this->data['slugs'] ?? null,
            $this->getDefaultLanguage(),
            $this->getLanguages()
        );
    }

    public function isOnline() : bool
    {
        return true;
    }

    public function get(string $path, ?Language $language = null)
    {
        $data = ArrayHelper::getDot(explode('.', $path), $this->data);
        if (null === $data || is_string($data) || null === $language) {
            return $data;
        }
        return $this->localize($data, $language);
    }

    public function localize(array $data, Language $language)
    {
        if ($this->isLoc($data)) {
            return $data[(string) $language]
                ?? $data[(string) $this->getDefaultLanguage()]
                ?? array_shift($data);
        }
        foreach ($data as &$val) {
            if (is_array($val)) {
                $val = $this->localize($val, $language);
            }
        }
        return $data;
    }

    /**
     * @return array<Language>
     */
    public function getLanguages() : array
    {
        return $this->languages;
    }

    public function getDefaultLanguage() : Language
    {
        return $this->languages[0];
    }

    public function getSlugs() : Slugs
    {
        return $this->slugs;
    }

    public function getRedirects() : ?array
    {
        return $this->data['redirects'] ?? [];
    }

    public function getPageName(string $page, Language $language) : string
    {
        if ('home' === $page) {
            switch ((string)$language) {
                case 'sv': return 'Hem';
                case 'fi': return 'Koti';
                default: return 'Home';
            }
        }

        $slug = $this->slugs->getSlug($page, $language);
        if (null === $slug) {
            echo $page;
            die();
        }

        if ($slug === '') {
            $slug = 'home';
        }

        if (is_null($this->pageNames)) {
            $this->pageNames = [];
            foreach ($this->data['pages'] as $p => $data) {
                if (null === $data) {
                    continue;
                }
                if (ArrayHelper::isAssociative($data)) {
                    $data = [$data];
                }
                foreach ($data as $section) {
                    if (isset($section['_name'])) {
                        $this->pageNames[$p] = $section['_name'];
                    }
                }
            }
        }

        if (isset($this->pageNames[$slug])) {
            return $this->pageNames[$slug];
        }

        $slug = explode('/', $slug);
        $last = array_pop($slug);

        return ucfirst(str_replace('-', ' ', $last));
    }

    public function getSections(string $page, Language $language) : array
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
        $all      = array_merge($before, $all, $after);
        $sections = [];
        foreach ($all as $section) {
            $type = $section['type'] ?? 'default';
            if ($this->hideSection($section, $page, $language)) {
                continue;
            }
            if (isset($section['repeat'])) {
                $sections = array_merge($sections, $this->getRepeated($section));
            } else {
                $sections[] = $section;
            }
        }
        return $this->localize($sections ?? [], $language) ?? [];
    }

    public function getMeta(string $page, Language $language) : ?array
    {
        $meta = [
            'title'       => [$this->get('name')],
            'description' => $this->get('description', $language),
            'keywords'    => $this->get('keywords', $language),
            'author'      => $this->get('author', $language),
        ];

        if ($language != $this->languages[0]) {
            $meta['title'][0] .= ' ('.strtoupper((string)$language).')';
        }

        $data = $this->data['pages'][$page];
        if (null === $data) {
            $meta['title'] = implode(' - ', $meta['title']);
            return $meta;
        }
        if (ArrayHelper::isAssociative($data)) {
            $data = [$data];
        }

        $p     = explode('/', $page);
        $title = [];
        while (count($p) > 0) {
            $title[]  = $this->getPageName(implode('/', $p), $language);
            array_pop($p);
        }
        $meta['title'] =array_merge($title, $meta['title']);

        foreach ($data as $section) {
            if (isset($section['_meta'])) {
                $pageMeta = $this->localize($section['_meta'], $language);
                if (isset($pageMeta['title'])) {
                    array_pop($meta['title']);
                    $meta['title'][] = $pageMeta['title'];
                    unset($pageMeta['title']);
                }
                $meta = ArrayHelper::merge($meta, $pageMeta);
            }
        }

        $meta['title'] = implode(' - ', $meta['title']);

        // $all = $this->data['pages'][$page] ?? [];
        // foreach ($all as $section) {
        //     if (isset($section['_meta'])) {
        //         if ('meta' === $type) {
        //             $pageMeta = $this->localize($section, $language);
        //             $meta     = ArrayHelper::merge($meta, $pageMeta);
        //         }
        //     }
        // }
        // if (!isset($meta['title'])) {
        //     echo 'Hej';
        // }
        return $meta;
    }

    public function getLayout(string $page) : ?array
    {
        $all = array_merge(
            $this->data['before'] ?? [],
            $this->data['pages'][$page] ?? [],
            $this->data['after'] ?? []
        );
        foreach ($all as $section) {
            $type = $section['type'] ?? 'default';
            if ('layout' === $type) {
                return $section;
            }
        }
        return null;
    }

    public function getComponentFactories() : array
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

    private function hideSection(array $section, string $page, Language $language) : bool
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

    private function isLoc(array $data) : bool
    {
        $keys = array_keys($data);
        foreach ($keys as $key) {
            if (is_numeric($key)) {
                return false;
            }
            if (!in_array($key, $this->languageCodes)) {
                return false;
            }
        }
        return true;
    }

    private function parseLanguages() : void
    {
        $languages = $this->data['languages'] ?? null;
        $language  = $this->data['language'] ?? null;
        if (null === $languages && null !== $language) {
            $languages = [$language];
        } elseif (is_string($languages)) {
            $languages = explode(',', str_replace(' ', '', $languages));
        } elseif (!is_array($languages)) {
            $languages = ['en'];
        }
        foreach ($languages as $language) {
            $this->languages[]     = new Language($language);
            $this->languageCodes[] = $language;
        }
    }

    private function expandPages(?array $pages) : array
    {
        if (null === $pages) {
            return [];
        }
        $expandedPages = [];
        foreach ($pages as $page => $pageData) {
            if (false !== mb_strpos((string)$page, '[')) {
                $matches = [];
                preg_match_all('/\[([^\[\]]*)\]/', $page, $matches);
                $params       = $matches[0];
                $permutations = $this->getPermutations(0, count($params), $pageData['data']);
                // TODO add support for localized slugs
                foreach ($permutations as $permutation) {
                    $expandedPage = $page;
                    $sections     = $pageData['content'];
                    foreach ($params as $i => $param) {
                        $expandedPage = str_replace($param, (string)$permutation[$i], $expandedPage);
                        $sections     = ArrayHelper::strReplace($param, (string)$permutation[$i], $sections);
                    }
                    $dataMapper                   = new DataMapper();
                    $expandedPages[$expandedPage] = $dataMapper->apply($sections, $pageData['data']);
                }
            } else {
                $expandedPages[$page] = $pageData;
            }
        }
        return $expandedPages;
    }

    private function getPermutations(int $level, int $max, array $data, array $parents = []) : array
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

    private function getRepeated(array $data) : array
    {
        $repeat = $data['repeat'];
        foreach ($repeat as $key => &$val) {
            $val['key'] = $key;
        }
        unset($data['repeat']);
        if (!isset($data['sections'])) {
            $sections = [$data];
        } else {
            $sections = $data['sections'];
        }

        $dataMapper                   = new DataMapper();
        $repeated                     = [];
        foreach ($repeat as $data) {
            $repeated = array_merge($repeated, $dataMapper->apply($sections, $data));
        }

        return $repeated;
    }
}

class DataMapper
{
    public function apply(array $tpl, array $data) : array
    {
        $dot = new \Adbar\Dot($data);
        return $this->search($tpl, $dot);
    }

    private function search(array $tpl, \Adbar\Dot $dot) : array
    {
        foreach ($tpl as $attr => &$value) {
            if (is_array($value)) {
                $value = $this->search($value, $dot);
            } elseif (is_string($value) && false !== mb_strpos($value, '{data.')) {
                $matches = [];
                preg_match_all('/\{data\.(.*?)\}/', $value, $matches);
                foreach ($matches[1] as $i => $replace) {
                    $replace = $dot->get($replace);
                    if (is_array($replace)) {
                        $value = $replace;
                    } else {
                        $value = str_replace($matches[0][$i], $replace ?? '', $value);
                    }
                }
            }
        }
        return $tpl;
    }
}
