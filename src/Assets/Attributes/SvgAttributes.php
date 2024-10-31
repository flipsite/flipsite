<?php

declare(strict_types=1);

namespace Flipsite\Assets\Attributes;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Sources\AbstractAssetInfo;

class SvgAttributes implements ImageAttributesInterface
{
    private ?int $width = null;
    private ?int $height = null;
    public function __construct(private AbstractAssetInfo $assetInfo, private AssetSourcesInterface $assetSources)
    {
    }

    public function getSrc(): string
    {
        $src = $this->assetInfo->getFilename();
        $src = str_replace('.svg', '.'.$this->assetInfo->getHash().'.svg', $src);
        return $this->assetSources->addBasePath($this->assetInfo->getType(), $src);
    }

    public function getSrcset(?string $type = null): ?string
    {
        return null;
    }

    public function getWidth(): ?int
    {
        if (null === $this->width) {
            $this->loadSize();
        }
        return $this->width;
    }

    public function getHeight(): ?int
    {
        if (null === $this->height) {
            $this->loadSize();
        }
        return $this->height;
    }
    private function loadSize()
    {
        $data = new \Flipsite\Utils\SvgData($this->assetInfo->getContents());
        $this->width = $data->getWidth();
        $this->height = $data->getHeight();
    }
}
