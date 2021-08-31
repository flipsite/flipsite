<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleDivide extends AbstractRule
{
    use Traits\ColorTrait;

    protected ?string $childCombinator = '* + *';

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if ($this->setColor($args, 'border-color', '--tw-divide-opacity')) {
            return;
        }
        $styles = ['solid','dashed','dotted','double','none'];
        if (in_array($args[0], $styles)) {
            $this->setDeclaration('border-style', $args[0]);
        }

        $direction = array_shift($args);
        if (in_array($direction, ['x','y'])) {
            if ('reverse' === ($args[0] ?? '')) {
                $this->setDeclaration('--tw-divide-'.$direction.'-reverse', 1);
            } else {
                array_shift($args);
                $value = $this->getConfig('divideWidth', $args[0] ?? 'DEFAULT');
                $value ??= $this->checkCallbacks('size', $args);
                $this->setDeclaration('--tw-divide-'.$direction.'-reverse', 0);
                $borders = 'x' === $direction ? ['left','right'] :['bottom','top'];
                $this->setDeclaration('border-'.$borders[0].'-width', 'calc('.$value.' * var(--tw-divide-'.$direction.'-reverse))');
                $this->setDeclaration('border-'.$borders[1].'-width', 'calc('.$value.' * calc(1 - var(--tw-divide-'.$direction.'-reverse)))');
            }
        }
    }
}
