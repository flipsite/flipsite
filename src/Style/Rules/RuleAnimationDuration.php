<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleAnimationDuration extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = intval($args[0]).'ms';
        $this->setDeclaration('animation-duration', $value);
    }
}
