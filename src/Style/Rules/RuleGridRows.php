<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleGridRows extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $arg0   = $args[0];
        $config = $this->getConfig('gridTemplateRows');

        if (1 === count($args) && isset($config[$arg0])) {
            $this->setDeclaration('grid-template-rows', $config[$arg0]);
            return;
        }

        if (1 === count($args) && is_numeric($arg0)) {
            $this->setDeclaration('grid-template-rows', 'repeat('.intval($arg0).',minmax(0,1fr))');
            return;
        }
    }
}
