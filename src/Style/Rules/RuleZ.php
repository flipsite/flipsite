<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleZ extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value ??= $this->getConfig('zIndex', $args[0]);
        $value = intval($args[0]);
        $this->setDeclaration('z-index', $value);
    }
}
