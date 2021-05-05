<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleMaxW extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = null;
        if (isset($args[1]) && 'screen' === $args[0]) {
            $tmp   = explode('/', $args[1]);
            $value = $this->getConfig('screens', $tmp[0]);
            if (isset($tmp[1])) {
                $value = intval(floatval($value) / floatval($tmp[1])).'px';
            }
        }
        $value ??= $this->getConfig('maxWidth', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);
        $this->setDeclaration('max-width', $value);
    }
}
