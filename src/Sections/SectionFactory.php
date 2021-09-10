<?php

declare(strict_types=1);

namespace Flipsite\Sections;

use Symfony\Component\Yaml\Yaml;

class SectionFactory extends AbstractSectionFactory
{
    public function getStyle(string $type) : array
    {
        $filePath = __DIR__.'/../../yaml/sections/'.$type.'.yaml';
        if (file_exists($filePath)) {
            return Yaml::parseFile($filePath);
        }
        return [];
    }
}
