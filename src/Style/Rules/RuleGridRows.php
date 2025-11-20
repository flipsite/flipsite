<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleGridRows extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $this->setDeclaration('grid-template-rows', 'repeat('.intval($args[0]).',minmax(0,1fr))');
    }
}
