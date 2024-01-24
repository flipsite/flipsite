<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

use Flipsite\Assets\Sources\AssetSourcesInterface;
use Flipsite\Assets\Sources\AbstractAssetInfo;
use Flipsite\Assets\Options\RasterOptions;

class ImageAttributes extends AbstractImageAttributes
{
    private string $image;
    private string $hash;
    private string $extension;
    private string $useExtension;
    private ?RasterOptions $options = null;

    public function __construct(array $options, private AbstractAssetInfo $assetInfo, private AssetSourcesInterface $assetSources)
    {
        $this->image     = $assetInfo->getFilename();
        $pathinfo        = pathinfo($assetInfo->getFilename());
        $this->extension = $pathinfo['extension'];
        if (in_array($this->extension, ['png', 'jpg', 'jpeg'])) {
            $this->useExtension = ($options['webp'] ?? true) ? 'webp' : $this->extension;
        } else {
            $this->useExtension = $this->extension;
        }
        $this->hash = $assetInfo->getHash();
        $this->setSize($options, $assetInfo->getWidth(), $assetInfo->getHeight());

        $options['width']  = $this->width;
        $options['height'] = $this->height;
        $this->width       = intval($options['width']);
        $this->height      = intval($options['height']);

        if ('gif' !== $this->extension) {
            $this->srcset = $options['srcset'] ?? null;
            unset($options['aspectRatio'], $options['srcset']);
            $this->options = new RasterOptions($options);
        }
        $this->src = $this->buildSrc();
    }

    private function buildSrc() : string
    {
        $replace = $this->options.'.'.$this->hash. '.'.$this->useExtension;
        $src     = str_replace('.'.$this->extension, $replace, $this->image);
        return $this->assetSources->addBasePath($this->assetInfo->getType(), $src);
    }

    public function getSrcset(?string $type = null): ?string
    {
        $srcset = [];
        if (!$this->srcset) {
            return null;
        }
        foreach ($this->srcset as $variant) {
            preg_match('/[0-9\.]+[w|x]/', $variant, $matches);
            if (0 === count($matches)) {
                throw new \Exception('Invalid srcset variant (' . $variant . '). Should be multiplier (1x, 1.5x) or width (100w, 300w)');
            }
            if (false !== mb_strpos($variant, 'x')) {
                $this->options->changeScale(floatval(trim($variant, 'x')));
            } else {
                $width = floatval(trim($variant, 'w'));
                $scale = $width / floatval($this->options->getValue('width'));
                $this->options->changeScale($scale);
            }
            $srcset[] = new ImageSrcset($this->buildSrc(), $variant, $type);
        }
        $this->options->changeScale();
        if (!count($srcset)) {
            return null;
        }
        return implode(', ', $srcset);
    }
}
