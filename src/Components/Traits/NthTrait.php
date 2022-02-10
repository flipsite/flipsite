<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use Flipsite\Utils\ArrayHelper;

trait NthTrait
{
    private ?array $nth = null;

    private function getNth(int $index, int $total, array $style) : array
    {
        $nth = $style['all'] ?? [];
        unset($style['all']);

        if ($index % 2 === 0 && isset($style['even'])) {
            $nth = ArrayHelper::merge($nth, $style['even']);
        } elseif ($index % 2 === 1 && isset($style['odd'])) {
            $nth = ArrayHelper::merge($nth, $style['odd']);
        }
        if (isset($style[$index])) {
            $nth = ArrayHelper::merge($nth, $style[$index]);
        }
        if (isset($style['first']) && $index === 0) {
            $nth = ArrayHelper::merge($nth, $style['first']);
        }
        if (isset($style['last']) && $index === $total - 1) {
            $nth = ArrayHelper::merge($nth, $style['last']);
        }

        if (isset($style['nth'])) {
            if (null === $this->nth) {
                $this->nth    = [];
                $parser       = new \MathParser\StdMathParser();
                $evaluator    = new \MathParser\Interpreting\Evaluator();
                foreach ($style['nth'] as $exp => $nthStyle) {
                    $ast   = $parser->parse($exp);
                    $i     = 0;
                    $n     = 0;
                    while ($i < $total) {
                        $evaluator->setVariables(['n' => $n++]);
                        $i             = intval($ast->accept($evaluator));
                        $this->nth[$i] = $nthStyle;
                    }
                }
            }
            $nth = ArrayHelper::merge($nth, $this->nth[$index] ?? []);
        }
        return $nth;
    }
}
