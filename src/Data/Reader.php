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

    public function __construct(Enviroment $enviroment)
    {
        $this->enviroment = $enviroment;
        $siteDir          = $this->enviroment->getSiteDir();
        if (file_exists($siteDir.'/site.yaml')) {
            $this->data = YamlExpander::parseFile($siteDir.'/site.yaml');
        } else {
            throw new NoSiteFileFoundException($siteDir);
        }
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

    public function getSections(string $page, Language $language) : array
    {
        $all = array_merge(
            $this->data['before'] ?? [],
            $this->data['pages'][$page] ?? [],
            $this->data['after'] ?? []
        );
        $sections = [];
        foreach ($all as $section) {
            $type = $section['type'] ?? 'default';
            if ('default' !== $type || $this->hideSection($section, $language)) {
                continue;
            }
            $sections[] = $section;
        }
        return $this->localize($sections, $language);
    }

    public function getMeta(string $page, Language $language) : ?array
    {
        $meta = [
            'description' => $this->get('description', $language),
            'keywords'    => $this->get('keywords', $language),
            'author'      => $this->get('author', $language),
        ];
        $all = $this->data['pages'][$page];
        foreach ($all as $section) {
            $type = $section['type'] ?? 'default';
            if ('meta' === $type) {
                $pageMeta = $this->localize($section, $language);
                $meta     = ArrayHelper::merge($meta, $pageMeta);
            }
        }
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

    private function hideSection(array $section, Language $language) : bool
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
        $language  = $this->data['language']  ?? null;
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
}
