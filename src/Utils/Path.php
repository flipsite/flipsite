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
    private array $languages  = [];
    private ?string $page     = '404';
    private ?string $redirect = null;

    /**
     * @param array<Language> $languages
     * */
    public function __construct(string $path, Language $default, array $languages, Slugs $slugs)
    {
        if ($path === 'home') {
            $path = '';
        }
        $this->languages = $languages;
        $all             = $slugs->getAll();
        if (isset($all[$path])) {
            $this->page = $all[$path][(string)$default];
            foreach ($all[$path] as $language => $page) {
                if ($path === $page) {
                    $this->language = new Language($language);
                    return;
                }
            }
        }
        $this->language = $languages[0];
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getPage(): ?string
    {
        if ('' === $this->page) {
            return 'home';
        }
        return $this->page;
    }
}
