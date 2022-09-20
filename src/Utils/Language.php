<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use TypeError;

final class Language
{
    private string $language;
    /**
     * @var array<string>
     */
    const LANGUAGES = ['aa', 'ab', 'ae', 'af', 'ak', 'am', 'an', 'ar',
        'as', 'av', 'ay', 'az', 'ba', 'be', 'bg', 'bh', 'bi', 'bm', 'bn', 'bo',
        'br', 'bs', 'ca', 'ce', 'ch', 'co', 'cr', 'cs', 'cu', 'cv', 'cy', 'da',
        'de', 'dv', 'dz', 'ee', 'el', 'en', 'eo', 'es', 'et', 'eu', 'fa', 'ff',
        'fi', 'fj', 'fo', 'fr', 'fy', 'ga', 'gd', 'gl', 'gn', 'gu', 'gv', 'ha',
        'he', 'hi', 'ho', 'hr', 'ht', 'hu', 'hy', 'hz', 'ia', 'id', 'ie', 'ig',
        'ii', 'ik', 'io', 'is', 'it', 'iu', 'ja', 'jv', 'ka', 'kg', 'ki', 'kj',
        'kk', 'kl', 'km', 'kn', 'ko', 'kr', 'ks', 'ku', 'kv', 'kw', 'ky', 'la',
        'lb', 'lg', 'li', 'ln', 'lo', 'lt', 'lu', 'lv', 'mg', 'mh', 'mi', 'mk',
        'ml', 'mn', 'mr', 'ms', 'mt', 'my', 'na', 'nb', 'nd', 'ne', 'ng', 'nl',
        'nn', 'no', 'nr', 'nv', 'ny', 'oc', 'oj', 'om', 'or', 'os', 'pa', 'pi',
        'pl', 'ps', 'pt', 'qu', 'rm', 'rn', 'ro', 'ru', 'rw', 'sa', 'sc', 'sd',
        'se', 'sg', 'si', 'sk', 'sl', 'sm', 'sn', 'so', 'sq', 'sr', 'ss', 'st',
        'su', 'sv', 'sw', 'ta', 'te', 'tg', 'th', 'ti', 'tk', 'tl', 'tn', 'to',
        'tr', 'ts', 'tt', 'tw', 'ty', 'ug', 'uk', 'ur', 'uz', 've', 'vi', 'vo',
        'wa', 'wo', 'xh', 'yi', 'yo', 'za', 'zh', 'zu',
    ];

    public function __construct(string $language)
    {
        if (!in_array($language, self::LANGUAGES)) {
            throw new TypeError('Invalid language ('.$language.')');
        }
        $this->language = $language;
    }

    public static function getList() : array
    {
        return self::LANGUAGES;
    }

    public function __toString() : string
    {
        return $this->language;
    }

    /**
     * @param Language|string $language
     */
    public function isSame($language) : bool
    {
        return $this->language === (string) $language;
    }

    public function upper() : string
    {
        return mb_strtoupper($this->language);
    }

    public function getLocale() : string
    {
        if ('en' === $this->language) {
            return 'en_US';
        }
        if ('sv' === $this->language) {
            return 'sv_SE';
        }
        return mb_strtoupper($this->language.'_'.$this->language);
    }
}
