<?php

declare(strict_types=1);

namespace Flipsite\Utils;

use Flipsite\EnvironmentInterface;

final class Robots
{
    public function __construct(private EnvironmentInterface $environment)
    {
    }

    public function __toString() : string
    {
        if ($this->environment->isProduction()) {
            $rows = [
                'User-agent: *',
                'Allow: /',
                'Sitemap: '.$this->environment->getAbsoluteUrl('sitemap.xml'),
            ];
        } else {
            $rows = [
                'User-agent: *',
                'Disallow: /',
                '# No sitemap if not live',
                '# Sitemap: '.$this->environment->getAbsoluteUrl('sitemap.xml')
            ];
        }
        return implode("\n", $rows);
    }
}
