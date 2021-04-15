<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

abstract class AbstractRuleOpacity extends AbstractRule
{
    protected string $var;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $opacity = $this->getConfig('opacity', $args[0]);
        if (null === $opacity) {
            $opacity = floatval($args[0]) / 100.0;
            if ($opacity < 0.0) {
                $opacity = 0.0;
            }
            if ($opacity > 1.0) {
                $opacity = 1.0;
            }
        }
        if (null !== $opacity) {
            $this->setDeclaration($this->var, $opacity.'!important');
        }
    }
}
