<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleSkewY extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('skew', $args[0]);
        $value ??= $args[0].'deg';
        $this->setDeclaration('--tw-skew-y', $value);
    }
}
