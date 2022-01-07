<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleStroke extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if ($this->setColor($args, 'stroke')) {
            return;
        }
        $value = $this->getConfig('strokeWidth', $args[0]);
        $value ??= intval($args[0]);
        $this->setDeclaration('stroke-width', $value);
    }
}
