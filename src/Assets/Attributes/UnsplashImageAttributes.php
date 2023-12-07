<?php

declare(strict_types=1);
namespace Flipsite\Assets\Attributes;

class UnsplashImageAttributes extends AbstractImageAttributes
{
    private string $urlTpl;
    
    public function __construct(string $url, array $options)
    {
        preg_match('/[?&]w=([^&]+)/', $url, $matchesW);
        preg_match('/[?&]h=([^&]+)/', $url, $matchesH);


        $this->width  = intval($matchesW[1] ?? null);
        $this->height = intval($matchesH[1] ?? null);

        // $tmp = explode('?', $url);
        // $query = explode('&', $tmp[1]);
        // foreach ($query as $q) {
        //     $tmp2 = explode('=', $q);
        //     if ($tmp2[0] === 'w') {
        //         $this->realWidth = intval($tmp2[1]);
        //     } elseif ($tmp2[0] === 'h') {
        //         $this->realHeight = intval($tmp2[1]);
        //     } else {
        //         $this->query[$tmp2[0]] = $tmp2[1];
        //     }
        // }

        // $this->image  = $tmp[0];
        // $this->srcset = $options['srcset'] ?? null;
        // $size         = $this->getSize($options);
        // $this->width  = intval($size['width']);
        // $this->height = intval($size['height']);
    }
}
