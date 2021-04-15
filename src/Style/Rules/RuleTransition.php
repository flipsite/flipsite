<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleTransition extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('transitionProperty', $args[0] ?? 'DEFAULT');
        $this->setDeclaration('transition-property', $value);
    }
}
