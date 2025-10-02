<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleColEnd extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $this->setDeclaration('grid-column-end', $args[0]. ' !important');
    }
}
