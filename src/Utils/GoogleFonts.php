<?php

declare(strict_types=1);
namespace Flipsite\Utils;

class GoogleFonts
{
    public function __construct(private array $fonts)
    {
    }

    public function download(string $targetDir, string $fontDir, string $basePath, array $subsets = ['latin', 'math', 'symbols']) : array
    {
        $url = $this->getUrl();
        if (!$url) {
            return $this->fonts;
        }
        $fontFaces = $this->parseGoogleFile($url, $subsets);

        $files = [];
        foreach ($fontFaces as $fontFace) {
            $family = $fontFace['font-family'];

            $familyId   = str_replace(' ', '-', strtolower($family));
            $binaryData = file_get_contents($fontFace['src']);
            $hash       = substr(md5($binaryData), 0, 6);
            $filename   = $fontDir.'/'.$familyId.'-'.$fontFace['subset'].'.'.$hash.'.woff2';
            if (!file_exists($targetDir.'/'.$fontDir)) {
                mkdir($targetDir.'/'.$fontDir, 0777, true);
            }
            file_put_contents($targetDir.'/'.$filename, $binaryData);

            $url    = $basePath.$filename;
            $file   = [
                'style'         => $fontFace['font-style'],
                'weight'        => $fontFace['font-weight'],
                'display'       => $fontFace['font-display'],
                'src'           => 'url('.$url.') format("woff2")',
                'unicode-range' => $fontFace['unicode-range']
            ];
            $files[$family][] = $file;
        }
        $fonts = $this->fonts;
        foreach ($fonts as &$font) {
            $family = $font['family'];
            if (isset($files[$family])) {
                $font['provider'] = 'local';
                $font['files']    = $files[$family];
            }
        }

        return $fonts;
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

    private function parseGoogleFile(string $googleUrl, array $subsets): array
    {
        // Initialize cURL
        $ch = curl_init($googleUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return the response as a string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects if any
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]);

        // Execute the cURL request
        $css = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        // Regular expression to match @font-face rules
        $pattern = '/\/\* (.*?) \*\/\s*@font-face\s*\{([^}]+)\}/';

        // Initialize array to store parsed font data
        $fonts = [];

        // Find all @font-face blocks
        preg_match_all($pattern, $css, $matches, PREG_SET_ORDER);

        // Process each matched @font-face block
        foreach ($matches as $match) {
            $fontKey    = $match[1]; // cyrillic-ext, cyrillic, etc.
            $properties = $match[2];

            // Parse properties in each @font-face block
            preg_match_all('/([a-z\-]+)\s*:\s*([^;]+);/', $properties, $propMatches, PREG_SET_ORDER);

            // Initialize array to store the properties of this font
            $fontData = [];

            foreach ($propMatches as $prop) {
                $propKey   = trim($prop[1]);
                $propValue = trim($prop[2]);

                // Remove quotes from font-family if they exist
                if ($propKey == 'font-family') {
                    $propValue = trim($propValue, '\'\"');
                }
                if ('src' === $propKey) {
                    $propValue = substr($propValue, 4, strlen($propValue) - 21);
                }

                // Add to the array
                $fontData[$propKey] = $propValue;
            }

            // Add to fonts array
            if (in_array($fontKey, $subsets)) {
                $fontData['subset'] = $fontKey;
                $fonts[]            = $fontData;
            }
        }
        return $fonts;
    }
}
