<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

class RuleAnimate extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('animation', $args[0]);
        $this->setDeclaration('animation', $value);
    }
}
