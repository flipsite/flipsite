<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleH extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->checkCallbacks('size', $args);
        $value ??= $this->getConfig('height', $args[0]);
        $value ??= $this->getConfig('spacing', $args[0]);
        $this->setDeclaration('height', $value);
    }
}
