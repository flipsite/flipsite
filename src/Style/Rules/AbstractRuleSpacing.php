<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleSpacing extends AbstractRule
{
    /**
     * @var array<string>
     */
    protected array $properties = [];

    protected ?array $safeAreaInset = null;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('spacing', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);

        $safe = $this->safeAreaInset && in_array('safe', $args);
        foreach ($this->properties as $i => $property) {
            if ($safe) {
                $safeValue = 'calc('.$value.' + env('.$this->safeAreaInset[$i].'))';
                $this->setDeclaration($property, $safeValue);
            } else {
                $this->setDeclaration($property, $value);
            }
        }
    }
}
