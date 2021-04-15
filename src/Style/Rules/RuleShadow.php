<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleShadow extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('boxShadow', $args[0] ?? 'DEFAULT');
        $this->setDeclaration('box-shadow', $value);
    }
}
