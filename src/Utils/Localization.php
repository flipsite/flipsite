<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class Localization implements \JsonSerializable
{
    private array $values = [];

    public function __construct(private array $languages, string $json)
    {
        if (self::isLocalization($json)) {
            $values = json_decode($json, true);
            if (count($languages) === 0) {
                foreach ($values as $language => $value) {
                    if (is_string($language) && is_string($value)) {
                        $this->languages[]       = $language;
                        $this->values[$language] = $value;
                    }
                }
            } else {
                foreach ($languages as $language) {
                    $language = (string)$language;
                    if (isset($values[$language])) {
                        $this->values[$language] = $values[$language];
                    }
                }
            }
        } else {
            $language                = (string)$languages[0];
            $this->values[$language] = $json;
        }
    }

    public static function isLocalization(string $value): bool
    {
        return strpos($value, '"_loc":true') !== false;
    }

    public function getValue(?Language $language = null): ?string
    {
        $language = (string)$language;
        if (isset($this->values[$language])) {
            return $this->values[$language];
        }
        $language = (string)$this->languages[0];
        return $this->values[$language] ?? null;
    }

    public function setValue(Language $language, string $value)
    {
        $language                = (string)$language;
        $this->values[$language] = $value;
    }

    public function jsonSerialize(): mixed
    {
        return array_merge(['_loc' => true], $this->values);
    }
}
