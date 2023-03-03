<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleMaxH extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->checkCallbacks('size', $args);
        $value ??= $this->getConfig('maxHeight', $args[0]);
        $this->setDeclaration('max-height', $value);
    }
}
