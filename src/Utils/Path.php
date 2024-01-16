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
    public function __construct(string $path, Language $default, array $languages, Slugs $slugs)
    {
        $this->languages = $languages;
        $parts           = explode('/', $path);
        $pathLanguage    = $this->parsePathLanguage(array_shift($parts));
        if (null !== $pathLanguage) {
            $pathWithoutLanguage = implode('/', $parts);
        } else {
            $pathWithoutLanguage = $path;
        }

        $this->page = $slugs->getPage($pathWithoutLanguage);
        $this->language = $pathLanguage ?? $slugs->getLanguage($pathWithoutLanguage) ?? $default;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    private function parsePathLanguage(string $path): ?Language
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
