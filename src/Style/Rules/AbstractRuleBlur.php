<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleBlur extends AbstractRule
{
    /**
     * @var array<string>
     */
    protected array $properties = [];

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->checkCallbacks('size', $args);
        $value ??= $this->getConfig('blur', $args[0] ?? 'DEFAULT');
        $this->setDeclaration($this->properties[0], 'blur('.$value.')');
    }
}
