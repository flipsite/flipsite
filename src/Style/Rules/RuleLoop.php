<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleLoop extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = intval($args[0]);
        $this->setDeclaration('--tw-animation-duration', $value.'s');
    }
}
