<?php

declare(strict_types=1);
namespace Flipsite\Utils;

class GoogleFonts
{
    public function __construct(private array $fonts)
    {
    }

    public function getUrl() : ?string
    {
        $fonts = array_filter($this->fonts, function ($value, $key) {
            return is_array($value) && 'google' === trim(mb_strtolower($value['provider'] ?? ''));
        }, ARRAY_FILTER_USE_BOTH);
        if (!$fonts) {
            return null;
        }
        // $googleFonts = new GoogleFonts();
        // return $googleFonts->getUrl();
        foreach ($fonts as &$font) {
            $font = $this->normalizeGoogleFont($font);
        }
        $fonts = $this->mergeFonts($fonts);

        $families = [];
        $all      = [];
        foreach ($fonts as $font) {
            $param = $font['family'];
            if (isset($font['italic']) && count($font['italic'])) {
                foreach ($font['normal'] as $w) {
                    $all[] = '0,'.$w;
                }
                foreach ($font['italic'] as $w) {
                    $all[] = '1,'.$w;
                }
                $param .= ':ital,wght@'.implode(';', $all);
            } elseif (count($font['normal']) && $font['normal'] != [400]) {
                $param .= ':wght@'.implode(';', $font['normal']);
            }
            $families[] = $param;
        }
        return 'https://fonts.googleapis.com/css2?family='.implode('&family=', $families).'&display=swap';
    }

    private function normalizeGoogleFont(array $font) : array
    {
        $font['family'] = urlencode(trim($font['family']));
        $font['normal'] = $this->toIntArray($font['normal'] ?? []);
        $font['italic'] = $this->toIntArray($font['italic'] ?? $font['italics'] ?? []);
        return $font;
    }

    private function toIntArray($data) : array
    {
        if (is_int($data)) {
            return [$data];
        }
        if (is_string($data)) {
            $data = explode(',', str_replace(' ', '', $data));
        }
        array_walk($data, 'intval');
        return $data;
    }

    private function mergeFonts(array $fonts): array
    {
        $mergedFonts = [];
        foreach ($fonts as $font) {
            if (!isset($mergedFonts[$font['family']])) {
                $mergedFonts[$font['family']] = $font;
            } else {
                $mergedFont           = $mergedFonts[$font['family']];
                $mergedFont['normal'] = array_unique(array_merge($mergedFont['normal'] ?? [], $font['normal'] ?? []));
                $mergedFont['italic'] = array_unique(array_merge($mergedFont['italic'] ?? [], $font['italic'] ?? []));
                sort($mergedFont['normal']);
                sort($mergedFont['italic']);
                if (!count($mergedFont['normal'])) {
                    unset($mergedFont['normal']);
                }
                if (!count($mergedFont['italic'])) {
                    unset($mergedFont['italic']);
                }
                $mergedFonts[$font['family']] = $mergedFont;
            }
        }
        return array_values($mergedFonts);
    }
}
