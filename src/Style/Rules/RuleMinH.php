<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleMinH extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = null;
        $value ??= $this->getConfig('minHeight', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);
        $this->setDeclaration('min-height', $value);
    }
}
