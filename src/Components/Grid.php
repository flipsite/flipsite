<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

class Grid extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\RepeatTrait;
    use Traits\NthTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->addStyle($style);
        $children = [];
        foreach ($data['cols'] as $i => $colData) {
            if (is_array($colData)) {
                $type = $colData['type'] ?? $style['colType'] ?? 'group';
                unset($colData['type']);
            } else {
                $type = $style['colType'] ?? 'group';
            }
            $colStyle   = $this->getNth($i, count($data['cols']), $style['cols'] ?? []);
            $children[] = $this->builder->build($type, $colData, $colStyle, $appearance);
        }
        $this->addChildren($children);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_array($data) && isset($data['data'],$data['col'])) {
            // TODO maybe import file content here
            $data['cols'] = $this->expandRepeat($data['data'], $data['col']);
            unset($data['data'], $data['col']);
        }
        if (!is_array($data)) {
            throw new \Exception('Invalid component data');
        }
        if (!ArrayHelper::isAssociative($data)) {
            $data = ['cols' => $data];
        }
        return $data;
    }
}
