<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleGridCols extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $arg0   = $args[0];
        $config = $this->getConfig('gridTemplateColumns');

        if (1 === count($args) && isset($config[$arg0])) {
            $this->setDeclaration('grid-template-columns', $config[$arg0]);
            return;
        }

        if (1 === count($args) && is_numeric($arg0)) {
            $this->setDeclaration('grid-template-columns', 'repeat('.intval($arg0).',minmax(0,1fr))');
            return;
        }
    }
}
