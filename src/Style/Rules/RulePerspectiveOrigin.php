<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RulePerspectiveOrigin extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args): void
    {
        $value = $this->getConfig('transformOrigin', implode('-', $args));
        $this->setDeclaration('perspective-origin', $value);
    }
}
