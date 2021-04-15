<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleDuration extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('transitionDuration', $args[0]);
        $value ??= intval($args[0]).'ms';
        $this->setDeclaration('transition-duration', $value);
    }
}
