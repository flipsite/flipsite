<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleSpaceY extends AbstractRule
{
    protected ?string $childCombinator = '* + *';

    protected function process(array $args) : void
    {
        if ('reverse' === $args[0]) {
            $this->setDeclaration('--tw-space-y-reverse', 1);
            return;
        }
        $value = $this->getConfig('spacing', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);

        $this->setDeclaration('--tw-space-y-reverse', 0);
        $this->setDeclaration('margin-top', 'calc('.$value.' * calc(1 - var(--tw-space-y-reverse)))');
        $this->setDeclaration('margin-bottom', 'calc('.$value.' * var(--tw-space-y-reverse))');
    }
}
