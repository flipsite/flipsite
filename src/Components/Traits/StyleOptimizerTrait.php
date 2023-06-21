<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use Flipsite\Utils\StyleNthOptimizer;

trait StyleOptimizerTrait
{
    protected function optimizeStyle(array $style, int $index, int $total) : array
    {
        $hasModifier = function (string $value):bool {
            $keywords = ['even', 'odd', 'first', 'last'];
            foreach ($keywords as $keyword) {
                if (strpos($value, $keyword) !== false) {
                    return true;
                }
            }
            return false;
        };
        foreach ($style as &$value) {
            if (is_string($value) && $hasModifier($value)) {
                $so    = new StyleNthOptimizer($value);
                $value = $so->get($index, $total);
            }
        }
        return $style;
    }
}
