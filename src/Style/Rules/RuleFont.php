<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleFont extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('fontWeight', $args[0]);
        if ($value) {
            $this->setDeclaration('font-weight', $value);
            return;
        }

        $value = $this->getConfig('fontFamily', $args[0]);
        if ($value) {
            $value = is_array($value) ? implode(',', $value) : $value;
            $this->setDeclaration('font-family', $value);
            return;
        }

        $arg0   = $args[0];
        $config = $this->getConfig('fontWeight');

        if (1 === count($args) && isset($config[$arg0])) {
            $this->setDeclaration('font-weight', $config[$arg0]);
            return;
        }
    }
}
