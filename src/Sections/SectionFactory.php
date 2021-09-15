<?php

declare(strict_types=1);

namespace Flipsite\Sections;

use Flipsite\Utils\YamlExpander;

class SectionFactory extends AbstractSectionFactory
{
    public function getStyle(string $section) : ?array
    {
        $filePath = __DIR__.'/../../yaml/sections/'.$section.'.yaml';
        if (file_exists($filePath)) {
            return YamlExpander::parseFile($filePath);
        }
        return null;
    }
    public function getExample(string $section) : ?array
    {
        $filePath = __DIR__.'/../../yaml/examples/'.$section.'-example.yaml';
        if (file_exists($filePath)) {
            return YamlExpander::parseFile($filePath);
        }
        return null;
    }
}
