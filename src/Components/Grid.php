<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Grid extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $type = 'div';

    public function build(array $data, array $style, array $flags, string $appearance = 'light') : void
    {
        $this->addStyle($style['container'] ?? []);
        if (isset($style['colType']) || count($flags)) {
            $data = $this->addType($style['colType'] ?? $flags[0], $data);
        }
        foreach ($data as $i => $col) {
            $colStyle   = $this->getColStyle($i, $style);
            $components = [];
            foreach ($col as $componentType => $componentData) {
                $componentStyle = $colStyle[$componentType] ?? [];
                if (mb_strpos($componentType, ':')) {
                    $tmp            = explode(':', $componentType);
                    $componentStyle = ArrayHelper::merge($colStyle[$tmp[0]] ?? [], $componentStyle);
                }
                $components[] = $this->builder->build($componentType, $componentData, $componentStyle, $appearance);
            }
            if (isset($colStyle['container'])) {
                $col = new Element($colStyle['container']['type'] ?? 'div');
                unset($colStyle['container']['type']);
                $col->addStyle($colStyle['container']);
                $col->addChildren($components);
                $this->addChild($col);
            } else {
                $this->addChildren($components);
            }
        }
    }

    private function addType(string $type, array $data) : array
    {
        $components = [];
        foreach ($data as $data_) {
            $components[] = [$type => $data_];
        }
        return $components;
    }

    private function getColStyle(int $index, array $style) : array
    {
        $all = $style['colsAll'] ?? [];
        if (0 === $index % 2) {
            $oddEven = $style['colsEven'] ?? [];
        } else {
            $oddEven = $style['colsOdd'] ?? [];
        }
        return ArrayHelper::merge($all, $oddEven, $style['cols'][$index] ?? []);
    }
}
