<?php

declare(strict_types=1);
namespace Flipsite\Compiler;

class AssetParser
{
    public static function parse(string $html): array
    {
        $assets = [];

        $html = str_replace("\n", '', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = str_replace('> <', '><', $html);
        $html = str_replace('> ', '>', $html);
        $html = str_replace(' <', '<', $html);

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
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
        $linkTags = $doc->getElementsByTagName('link');
        foreach ($linkTags as $tag) {
            if ('icon' === $tag->getAttribute('rel')) {
                $url      = $tag->getAttribute('href');
            } elseif ('apple-touch-icon' === $tag->getAttribute('rel')) {
                $url      = $tag->getAttribute('href');
            }
            if (!str_starts_with($url, 'data:')) {
                $assets[] = $url;
            }
        }

        $matches = [];
        preg_match_all('/url\([^)]*\)/', $html, $matches);
        if (isset($matches[0])) {
            foreach ($matches[0] as $asset) {
                $asset = trim($asset,')');
                $asset = str_replace('url(','',$asset);
                $assets[] = $asset;
            }
        }

        $assets = array_values(array_unique($assets));

        // Remove http
        $assets = array_filter($assets, function ($asset) {
            return !str_starts_with($asset, 'http');
        });

        return $assets;
    }
}
