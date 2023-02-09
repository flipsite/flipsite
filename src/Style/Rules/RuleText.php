<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleText extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args): void
    {
        if ('transparent' === $args[0]) {
            $this->setDeclaration('color', 'transparent');
            return;
        }
        if ($this->setColor($args, 'color')) {
            return;
        }
        $fontSize = $this->getConfig('textSize', $args[0]);
        if (1 === count($args)) {
            if (is_array($fontSize) && isset($fontSize[1]['lineHeight'])) {
                $this->setDeclaration('font-size', $fontSize[0]);
                $this->setDeclaration('line-height', $fontSize[1]['lineHeight']);
                return;
            }
            if (is_string($fontSize)) {
                $this->setDeclaration('font-size', $fontSize);
            }
        }

        $value = $this->checkCallbacks('size', $args);
        if ($value) {
            $this->setDeclaration('font-size', $value);
        }
    }
}
