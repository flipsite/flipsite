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
    protected function process(array $args): void
    {
        if ($this->setColor($args, 'border-color')) {
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
                $value = $this->getConfig('divideWidth', $args[0] ?? 'DEFAULT');
                $value ??= $this->checkCallbacks('size', $args);
                $this->setDeclaration('--tw-divide-'.$direction.'-reverse', 0);
                if ('x' === $direction) {
                    $this->setDeclaration('border-right-width', 'calc('.$value.' * var(--tw-divide-x-reverse))');
                    $this->setDeclaration('border-left-width', 'calc('.$value.' * calc(1 - var(--tw-divide-x-reverse)))');
                    $this->setDeclaration('border-top', '0');
                    $this->setDeclaration('border-bottom', '0');
                } else {
                    $this->setDeclaration('border-top-width', 'calc('.$value.' * calc(1 - var(--tw-divide-y-reverse)))');
                    $this->setDeclaration('border-bottom-width', 'calc('.$value.' * var(--tw-divide-y-reverse))');
                    $this->setDeclaration('border-left', '0');
                    $this->setDeclaration('border-right', '0');
                }
            }
        }
    }
}
