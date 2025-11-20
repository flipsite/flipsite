<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleGridCols extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if ('none' === ($args[0] ?? '')) {
            $this->setDeclaration('grid-template-columns', 'none');
            return;
        }
        if (1 === count($args) && is_numeric($args[0])) {
            $this->setDeclaration('grid-template-columns', 'repeat('.intval($args[0]).',minmax(0,1fr))');
            return;
        }
    }
}
