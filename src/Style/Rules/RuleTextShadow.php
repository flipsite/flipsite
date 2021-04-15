<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleTextShadow extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('textShadow', $args[0] ?? 'DEFAULT');
        $this->setDeclaration('text-shadow', $value);
    }
}
