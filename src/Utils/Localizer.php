<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class Localizer
{
    private array $allLanguages;
    public function __construct(private array $languages)
    {
        $this->allLanguages = Language::getList();
    }

    public function localize(array $data, Language $language)
    {
        if ($this->isLoc($data)) {
            return $data[(string) $language]
                ?? $data[(string) $this->languages[0]]
                ?? array_shift($data);
        }
        foreach ($data as &$val) {
            if (is_array($val)) {
                $val = $this->localize($val, $language);
            }
        }
        return $data;
    }

    private function isLoc(array $data) : bool
    {
        $keys = array_keys($data);
        foreach ($keys as $key) {
            if (is_numeric($key)) {
                return false;
            }
            if (!in_array($key, $this->allLanguages)) {
                return false;
            }
        }
        return true;
    }
}
