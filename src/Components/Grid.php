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
        $children  = [];
        $totalCols = count($data['cols']);
        foreach ($data['cols'] as $i => $colData) {
            $colStyle = $this->getNth($i, $totalCols, $style['cols'] ?? []);
            if (is_array($colData)) {
                $type = $colData['type'] ?? 'group';
                unset($colData['type']);
            } else {
                $type = 'group';
            }
            if (isset($colStyle['type'])) {
                $type = $colStyle['type'];
                unset($colStyle['type']);
            }
            $type       = $colStyle['type'] ?? $type;
            $children[] = $this->builder->build($type, $colData, $colStyle, $appearance);
        }
        $this->addChildren($children);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_array($data) && isset($data['data'],$data['col'])) {
            // TODO maybe import file content here
            $data['cols'] = $this->expandRepeat($this->getCols($data['data'], $data['options'] ?? null), $data['col']);
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

    private function getCols(array $cols, ?array $options) : array
    {
        if (null === $options) {
            return $cols;
        }
        $offset  = $options['offset'] ?? 0;
        $length  = $options['length'] ?? 99999;
        return array_splice($cols, $offset, $length);
    }
}
