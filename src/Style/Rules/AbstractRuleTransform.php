<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleTransform extends AbstractRule
{
    /**
     * @var array<string>
     */
    protected array $properties = [];

    /**
     * @var array<string>
     */
    protected array $callbacks = [];

    protected string $unit = '';

    protected function process(array $args) : void
    {
        $value = null;
        switch ($this->unit) {
            case '%':
                $value ??= floatval($args[0] ?? 100) / 100.0;
                break;
            case 'deg':
                $value = UnitHelper::angle($args[0]);
                break;
        }
        if (null === $value) {
            foreach ($this->callbacks as $callback) {
                $value = $this->checkCallbacks($callback, $args);
                if (null !== $value) {
                    break;
                }
            }
        }
        if ($this->negative) {
            $this->negative = false;
            $value          = '-'.$value;
        }
        foreach ($this->properties as $property) {
            $this->setDeclaration($property, $value);
        }
        $this->setDeclaration('transform', 'perspective(var(--tw-perspective)) translateX(var(--tw-translate-x)) translateY(var(--tw-translate-y)) translateZ(var(--tw-translate-z)) rotate(var(--tw-rotate)) rotateX(var(--tw-rotate-x)) rotateY(var(--tw-rotate-y)) rotateZ(var(--tw-rotate-z)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y)) scaleZ(var(--tw-scale-z));');
    }
}
