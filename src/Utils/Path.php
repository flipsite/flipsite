<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use Flipsite\Data\Slugs;

final class Path
{
    private Language $language;
    /**
     * @var array<Language>
     */
    private array $languages = [];
    private ?string $page;
    private ?string $redirect = null;

    /**
     * @param array<Language> $languages
     * */
    public function __construct(string $path, Language $default, array $languages, Slugs $slugs, array $redirects = null)
    {
        if ('offline.html' === $path) {
            $this->language = $default;
            $this->page     = 'offline';
            return;
        }
        $this->languages = $languages;
        $parts           = explode('/', $path);
        $pathLanguage    = $this->parsePathLanguage(array_shift($parts));
        if (null !== $pathLanguage) {
            $pathWithoutLanguage = implode('/', $parts);
        } else {
            $pathWithoutLanguage = $path;
        }
        $this->page = $slugs->getPage($pathWithoutLanguage);
        $similar    = false;
        if (null === $this->page) {
            $this->page = $slugs->getSimilarPage($pathWithoutLanguage, 70.0);
            if (null !== $this->page) {
                $similar = true;
            }
        }
        $this->language = $pathLanguage ?? $slugs->getLanguage($pathWithoutLanguage) ?? $default;
        if (!$this->page) {
            if (is_array($redirects) && isset($redirects[$pathWithoutLanguage])) {
                $this->redirect = $redirects[$pathWithoutLanguage];
            } else {
                $this->page = '404';
            }
            return;
        }
        $slug = $slugs->getSlug($this->page, $this->language);
        if ($path !== $slug || $similar) {
            $this->redirect = $slug;
        }
    }

    public function getLanguage() : Language
    {
        return $this->language;
    }

    public function getRedirect() : ?string
    {
        return $this->redirect;
    }

    public function getPage() : ?string
    {
        return $this->page;
    }

    private function parsePathLanguage(string $path) : ?Language
    {
        if (2 !== mb_strlen($path)) {
            return null;
        }
        foreach ($this->languages as $language) {
            if ($language->isSame($path)) {
                return $language;
            }
        }
        return null;
    }
}
