<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleDropShadow extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('dropShadow', $args[0] ?? 'DEFAULT');
        if (is_string($value)) {
            $value = [$value];
        }
        foreach ($value as &$val) {
            $val = 'drop-shadow('.$val.')';
        }
        $this->setDeclaration('--tw-drop-shadow', implode(' ', $value));
    }
}
