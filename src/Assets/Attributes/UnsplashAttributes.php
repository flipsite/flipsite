<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

class UnsplashAttributes extends AbstractImageAttributes
{
    private string $srcTpl;
    
    public function __construct(string $src, array $options)
    {
        preg_match('/[?&]w=([^&]+)/', $src, $matchesW);
        preg_match('/[?&]h=([^&]+)/', $src, $matchesH);

        $this->setSize($options, intval($matchesW[1]), intval($matchesH[1]));

        $src = str_replace('&w='.$matchesW[1],'', $src);
        $src = str_replace('&h='.$matchesH[1],'', $src);

        $this->srcset = $options['srcset'] ?? null;

        $this->srcTpl = $src;
        if (isset($options['aspectRatio'])) {
            $this->srcTpl.='&fit=crop';
        }
    
        $this->src = $this->buildSrc($this->width,$this->height);
    }

    public function getSrcset(?string $type = null): ?string
    {
        $srcset = [];
        foreach ($this->srcset as $variant) {
            preg_match('/[0-9\.]+[w|x]/', $variant, $matches);
            if (0 === count($matches)) {
                throw new \Exception('Invalid srcset variant (' . $variant . '). Should be multiplier (1x, 1.5x) or width (100w, 300w)');
            }
            if (false !== mb_strpos($variant, 'x')) {
                $factor = floatval(trim($variant, 'x'));
                $srcset[] = new ImageSrcset(
                    $this->buildSrc(intval($this->width*$factor),intval($this->height*$factor)), 
                    $variant, $type);
            } else {
                // TODO
                // $width = floatval(trim($variant, 'w'));
                // $scale = $width / floatval($this->options->getValue('width'));
                // $this->options->changeScale($scale);
            }
        }
        if (!count($srcset)) {
            return null;
        }
        return implode(', ', $srcset);
    }
    private function buildSrc(int $width, int $height) {
        $src = $this->srcTpl;
        $src.='&w='.$width;
        $src.='&h='.$height;
        return $src;
        
    }
}

