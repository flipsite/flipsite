<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleSpaceX extends AbstractRule
{
    protected ?string $childCombinator = '* + *';

    protected function process(array $args) : void
    {
        if ('reverse' === $args[0]) {
            $this->setDeclaration('--tw-space-x-reverse', 1);
            return;
        }
        $value = $this->getConfig('spacing', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);

        $this->setDeclaration('--tw-space-x-reverse', 0);
        $this->setDeclaration('margin-right', 'calc('.$value.' * var(--tw-space-x-reverse))');
        $this->setDeclaration('margin-left', 'calc('.$value.' * calc(1 - var(--tw-space-x-reverse)))');
    }
}
