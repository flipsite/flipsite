<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleOutlineOffset extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $this->setDeclaration('outline-offset', intval($args[0]).'px');
    }
}
