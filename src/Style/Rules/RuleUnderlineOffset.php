<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleUnderlineOffset extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = intval($args[0]).'px';
        $this->setDeclaration('text-underline-offset', $value);
    }
}
