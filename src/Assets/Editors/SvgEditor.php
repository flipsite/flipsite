<?php

declare(strict_types=1);

namespace Flipsite\Assets\Editors;

use Flipsite\Assets\Options\SvgOptions;

final class SvgEditor extends AbstractImageEditor
{
    public function create() : void
    {
        $cachedFilename = $this->getCachedFilename();
        $this->fileSystem->copy($this->file->getFilename(), $cachedFilename);
        $svg     = file_get_contents($this->file->getFilename());
        $options = new SvgOptions($this->path);
        $fill    = $options->getValue('fill');
        if ($fill) {
            if (false !== mb_strpos($svg, 'fill="#000000"')) {
                $svg = str_replace('fill="#000000"', 'fill="'.$fill.'"', $svg);
            }
            $svg = str_replace('<svg', '<svg fill="'.$fill.'"', $svg);
        }
        file_put_contents($cachedFilename, $svg);
    }
}
