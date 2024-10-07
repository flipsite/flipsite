<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

class Iframe extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'iframe';

    public function build(array $data, array $style, array $options): void
    {
        $width = intval($data['width'] ?? 0);
        if ($width) {
            $this->setAttribute('width', $width);
        }
        $height = intval($data['height'] ?? 0);
        if ($height) {
            $this->setAttribute('height', $height);
        }
        if (isset($data['sandbox'])) {
            $list = ArrayHelper::decodeJsonOrCsv($data['sandbox']);
            $this->setAttribute('sandbox', implode(' ', $list));
        }
        $this->addStyle($style);
    }
}
