<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleLeading extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value ??= $this->getConfig('lineHeight', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);
        $this->setDeclaration('line-height', $value);
    }
}
