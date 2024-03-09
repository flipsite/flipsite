<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class Localization
{
    private array $values = [];
    public function __construct(private array $languages, string $json)
    {
        if (strpos($json, '"_loc":true') !== false) {        
            $values = json_decode($json, true);
            foreach ($languages as $language) {
                $language = (string)$language;
                if (isset($values[$language])) {
                    $this->values[$language] = $values[$language];
                }
            }
        } else {
            $language = (string)$languages[0];
            $this->values[$language] = $json;
        }
    }

    public function getValue(?Language $language = null) : ?string {
        $language = (string)$language;
        if (isset($this->values[$language])) {
            return $this->values[$language];
        }
        $language = (string)$this->languages[0];
        return $this->values[$language] ?? null;
    }
}
