<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleColStart extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $this->setDeclaration('grid-column-start', $args[0]);
    }
}
