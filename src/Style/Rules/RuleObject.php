<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleObject extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('objectPosition', $args[0]);
        $this->setDeclaration('object-position', $value);
    }
}
