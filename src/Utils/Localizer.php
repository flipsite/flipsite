<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class Localizer
{
    private array $allLanguages;
    private array $languagesCodes;

    public function __construct(private array $languages)
    {
        $this->allLanguages = Language::getList();
        foreach ($languages as $language) {
            $this->languagesCodes[] = (string)$language;
        }
    }

    public function localize(array $data, ?Language $language = null)
    {
        if ($this->isLoc($data)) {
            if ($language) {
                return $data[(string) $language]
                    ?? $data[(string) $this->languages[0]]
                    ?? array_shift($data);
            } else {
                $data['_loc'] = true;
                return json_encode($data);
            }
        }
        foreach ($data as &$val) {
            if (is_array($val)) {
                $val = $this->localize($val, $language);
            } else if (is_string($val) && strpos($val, '"_loc":true') !== false) {
                $val = json_decode($val, true);
                $val = $this->localize($val, $language);
            }
        }
        return $data;
    }

    private function isLoc(array $data) : bool
    {
        if ($data['_loc'] ?? false) {
            return true;
        }
        $keys = array_keys($data);
        if (count($keys) === 1) {
            return in_array((string)$keys[0], $this->languagesCodes);
        }
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
