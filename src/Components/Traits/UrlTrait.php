<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait UrlTrait
{
    use SlugsTrait;
    use PathTrait;
    use EnvironmentTrait;
    use ReaderTrait;

    private function url(?string $url, bool &$external) : ?string
    {
        if (!$url) {
            return null;
        }
        $external = false;
        // Just return if empty
        if ('#' === $url) {
            return '#';
        }

        if (str_starts_with($url, 'files/') && file_exists($this->environment->getSiteDir().'/'.$url)) {
            $basePath = $this->environment->getBasePath();
            return $basePath.'/'.$url;
        }
        $redirects = $this->reader->getRedirects();
        if (isset($redirects[$url])) {
            $url = $redirects[$url];
        }

        $parsed = parse_url($url);

        // Return if any scheme is available (absolute url, mailto, tel etc.)
        if (isset($parsed['scheme'])) {
            switch ($parsed['scheme']) {
                case 'tel':
                    return 'tel:+'.trim($parsed['path'], '+');
                case 'http':
                case 'https':
                    $external = true;
            }
            return $url;
        }

        // Return if inpage link e.g. #someSection
        if (!isset($parsed['path']) && isset($parsed['fragment'])) {
            return $url;
        }

        $path = $this->slugs->getPath($parsed['path'], $this->path->getLanguage(), $this->path->getPage());

        // Will always start with / or be null
        $parsed['path'] = $path;

        // Return missing URL if no path was found
        if (null === $parsed['path']) {
            return '#missing';
        }

        // Add base path
        $basePath = $this->environment->getBasePath();
        if (mb_strlen($basePath)) {
            $parsed['path'] = rtrim($basePath.$parsed['path'], '/');
        }

        $url = http_build_url($parsed);
        if (1 === mb_strlen($url)) {
            return $url;
        } 
        $url = rtrim($url, '/');
        if ($this->environment->hasTrailingSlash()) {
            $url.='/';
        }
        return $url;


    }
}
