<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleRowEnd extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $this->setDeclaration('grid-row-end', $args[0]);
    }
}
