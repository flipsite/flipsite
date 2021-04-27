<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleRotate extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('rotate', $args[0]);
        $value ??= UnitHelper::angle($args[0]);
        $this->setDeclaration('--tw-rotate', $value);
    }
}
