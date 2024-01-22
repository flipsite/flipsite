<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScrollbars extends AbstractRule
{
    protected ?string $pseudoElement = '::-webkit-scrollbar';
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        print_r($args);
        $this->setDeclaration('display', 'none');
    }
}
