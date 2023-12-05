<?php

declare(strict_types=1);

namespace Flipsite\Data;

use Flipsite\Utils\Language;

interface SiteDataInterface {
    public function getDefaultLanguage() : Language;
    public function getLanguages() : array;
    public function getSlugs() : Slugs;
    public function getHtmlStyle() : array;
    public function getBodyStyle(string $page) : array;
    public function getComponentStyle(string $component) : array;
}
