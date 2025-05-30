<?php

declare(strict_types=1);
namespace Flipsite\Compiler;

class AssetParser
{
    public static function parse(string $html, string $host): array
    {
        $assets = [];

        $html = str_replace("\n", '', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = str_replace('> <', '><', $html);
        $html = str_replace('> ', '>', $html);
        $html = str_replace(' <', '<', $html);

        libxml_use_internal_errors(true);
        $doc  = new \DOMDocument();
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $doc->loadHTML($html);

        $imgTags = $doc->getElementsByTagName('img');
        foreach ($imgTags as $tag) {
            $src = $tag->getAttribute('src');
            if (str_starts_with($src, 'data:')) {
                continue;
            }
            $assets[] = $src;
            $tmp      = explode(', ', $tag->getAttribute('srcset'));
            foreach ($tmp as $t) {
                $tmp2     = explode(' ', $t);
                $assets[] = $tmp2[0];
            }
        }

        $videoTags = $doc->getElementsByTagName('video');
        foreach ($videoTags as $tag) {
            $poster = $tag->getAttribute('poster');
            if ($poster) {
                $assets[] = $poster;
            }
        }

        $sourceTags = $doc->getElementsByTagName('source');
        foreach ($sourceTags as $tag) {
            $src = $tag->getAttribute('src');
            if ($src) {
                $assets[] = $src;
            }
        }

        $metaTags = $doc->getElementsByTagName('meta');
        foreach ($metaTags as $tag) {
            if ('og:image' === $tag->getAttribute('property')) {
                $url      = $tag->getAttribute('content');
                $assets[] = $url;
            }
        }

        $aTags = $doc->getElementsByTagName('a');
        foreach ($aTags as $tag) {
            $href = $tag->getAttribute('href');
            if ($href) {
                $tmp = explode('/files/', $href);
                if (count($tmp) === 2) {
                    $assets[] = '/files/'.$tmp[1];
                }
            }
        }

        $linkTags = $doc->getElementsByTagName('link');
        foreach ($linkTags as $tag) {
            if ('icon' === $tag->getAttribute('rel')) {
                $url = $tag->getAttribute('href');
            } elseif ('apple-touch-icon' === $tag->getAttribute('rel')) {
                $url = $tag->getAttribute('href');
            }
            if (isset($url) && !str_starts_with($url, 'data:')) {
                $assets[] = $url;
            }
        }

        $xpath    = new \DOMXPath($doc);
        $elements = $xpath->query('//*[@data-backgrounds]');
        foreach ($elements as $element) {
            $dataBg = $element->getAttribute('data-backgrounds');
            $json   = json_decode($dataBg) ?? [];
            foreach ($json as $image) {
                $assets[] = $image;
            }
        }

        $matches = [];
        preg_match_all('/url\([^)]*\)/', $html, $matches);
        if (isset($matches[0])) {
            foreach ($matches[0] as $asset) {
                $asset = trim($asset, ')');
                $asset = str_replace('url(', '', $asset);
                if (!str_starts_with($asset, '#')) {
                    $assets[] = $asset;
                }
            }
        }

        $assets = array_values(array_unique($assets));

        $internal = [];

        foreach ($assets as $asset) {
            $pathinfo = pathinfo($asset);
            if (!isset($pathinfo['extension'])) {
                continue;
            }
            if (!in_array($pathinfo['extension'], ['webp', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'mp4', 'mov', 'ogg', 'pdf', 'txt', 'csv', 'xls', 'vcf', 'ics', 'txt', 'csv'])) {
                continue;
            }
            if (str_starts_with($asset, 'http')) {
                $parsedUrl = parse_url($asset);
                if ($parsedUrl['host'] === $host) {
                    $internal[] = $parsedUrl['path'];
                }
            } else {
                $internal[] = $asset;
            }
        }

        return $internal;
    }
}
