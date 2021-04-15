<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleOpacity extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('opacity', $args[0]);
        $value ??= floatval($args[0]) / 100.0;
        $this->setDeclaration('opacity', $value);
    }
}
