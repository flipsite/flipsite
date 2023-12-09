<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Sources\VideoInfoInterface;

class VideoAttributes implements VideoAttributesInterface
{
    public function __construct(private VideoInfoInterface $videoInfo, private AssetSourcesInterface $assetSources)
    {
    }

    public function getSources() : array
    {
        $sources  = [];
        $filename = $this->videoInfo->getFilename();
        foreach ($this->videoInfo->getTypes() as $type) {
            $src       = $filename.'.'.$this->videoInfo->getHash($type).'.'.$type;
            $src       = $this->assetSources->addVideoBasePath($src);
            $sources[] = new SourceAttributes($src, 'video/'.$type);
        }
        return $sources;
    }
}
