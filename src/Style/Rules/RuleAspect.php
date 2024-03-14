<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleAspect extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $this->setDeclaration('aspect-ratio', $args[0]);
    }
}
