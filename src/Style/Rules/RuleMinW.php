<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleMinW extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->checkCallbacks('size', $args);
        $value ??= $this->getConfig('minWidth', $args[0]);
        $this->setDeclaration('min-width', $value);
    }
}
