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
        $wrapperStyle = $style['wrapper'] ?? false;
        if (false !== $wrapperStyle) {
            if (is_bool($wrapperStyle)) {
                $wrapperStyle = [];
            }
            $this->tag = $wrapperStyle['tag'] ?? 'div';
            unset($wrapperStyle['tag']);
            $this->addStyle($wrapperStyle);
            $content = new Element($style['tag'] ?? 'div');
            $content->addStyle($style);
            unset($style['tag']);
            $this->addChild($content);
        } else {
            $this->addStyle($style);
        }

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
            $children[] = $this->builder->build($type, $colData ?? [], $colStyle, $appearance);
        }

        if (false !== $wrapperStyle) {
            $content->addChildren($children);
        } else {
            $this->addChildren($children);
        }
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (!ArrayHelper::isAssociative($data)) {
            $data = ['cols' => $data];
        }
        if (isset($data['data'])) {
            $data['cols'] = $data['data'];
            unset($data['data']);
        }
        if ($data['options']['shuffle'] ?? false) {
            shuffle($data['cols']);
        }
        if ($data['options']['reverse'] ?? false) {
            $data['cols'] = array_reverse($data['cols']);
        }
        if (isset($data['options']['offset']) || isset($data['options']['length'])) {
            $offset       = $data['options']['offset'] ?? 0;
            $length       = $data['options']['length'] ?? 99999;
            $data['cols'] = array_splice($data['cols'], $offset, $length);
        }
        if (isset($data['options']['sort'])) {
            $tmp       = explode(' ', $data['options']['sort']);
            $sortField = $tmp[0];
            uasort($data['cols'], function ($a, $b) use ($sortField) {
                return $a[$sortField] <=> $b[$sortField];
            });
            if ('desc' === ($tmp[1] ?? 'asc')) {
                $data['cols'] = array_reverse($data['cols']);
            }
        }
        if (isset($data['col'])) {
            if (null === ($data['cols'] ?? null)) {
                $this->render = false;
            }
            $data['cols'] = $this->expandRepeat($data['cols'] ?? [], $data['col']);
            unset($data['col']);
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
