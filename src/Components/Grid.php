<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

class Grid extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\RepeatTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->addStyle($style);
        $children = [];
        if (isset($style['colsNth'])) {
            $nth = $this->getNth($style['colsNth'], count($data['cols']));
            unset($style['colsNth']);
            $style['cols'] = ArrayHelper::merge($style['cols'] ?? [], $nth);
        }
        foreach ($data['cols'] as $i => $colData) {
            if (is_array($colData)) {
                $type = $colData['type'] ?? $style['colType'] ?? 'group';
                unset($colData['type']);
            } else {
                $type = $style['colType'] ?? 'group';
            }
            $colStyle   = $this->getColStyle($i, count($data['cols']), $style);
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

    private function getColStyle(int $index, int $count, array $style) : array
    {
        $colStyle = $style['colsAll'] ?? [];
        if ($index % 2 === 0 && isset($style['colsEven'])) {
            $colStyle = ArrayHelper::merge($colStyle, $style['colsEven']);
        } elseif ($index % 2 === 1 && isset($style['colsOdd'])) {
            $colStyle = ArrayHelper::merge($colStyle, $style['colsOdd']);
        }
        if (isset($style['cols'][$index])) {
            $colStyle = ArrayHelper::merge($colStyle, $style['cols'][$index]);
        }

        if ($index === $count - 1 && isset($style['cols']['last'])) {
            $colStyle = ArrayHelper::merge($colStyle, $style['cols']['last']);
        }
        return $colStyle;
    }

    private function getNth(array $nth, int $count) : array
    {
        $parser    = new \MathParser\StdMathParser();
        $evaluator = new \MathParser\Interpreting\Evaluator();
        $colsStyle = [];
        foreach ($nth as $exp => $style) {
            $ast   = $parser->parse($exp);
            $index = 0;
            $i     = 0;
            while ($index < $count) {
                $evaluator->setVariables(['n' => $i++]);
                $index             = intval($ast->accept($evaluator));
                $colsStyle[$index] = $style;
            }
        }
        return $colsStyle;
    }
}
