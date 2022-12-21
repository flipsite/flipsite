<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Rating extends AbstractGroup
{
    protected string $tag   = 'div';

    public function build(array $data, array $style, string $appearance): void
    {
        $starData = [];
        $this->addStyle($style);
        for ($i=0; $i < 5; $i++) {
            $starStyle = $style['icon'];
            if ($data['value'] > $i && isset($style['active'])) {
                $starStyle = ArrayHelper::merge($starStyle, $style['active']);
            }
            $starData['icon:'.$i] = [
                'src'    => 'zondicons/star-full',
                '_style' => $starStyle
            ];
        }
        unset($style['icon'], $style['active']);

        parent::build($starData, $style, $appearance);
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['value' => (int)$data];
        }
        return $data;
    }
}
