<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

use Flipsite\Style\CallbackInterface;

abstract class AbstractRule
{
    protected bool $negative = false;
    /**
     * @var array<string>
     * */
    protected array $declarations = [];

    protected ?string $childCombinator = null;

    protected array $config;
    private CallbackInterface $callbacks;

    public function __construct(array $args, bool $negative, array &$config, CallbackInterface $callbacks = null)
    {
        $this->negative  = $negative;
        $this->config    = $config;
        $this->callbacks = $callbacks;
        $this->process($args);
    }

    public function getDeclarations() : string
    {
        $declarations = [];
        foreach ($this->declarations as $property => $value) {
            if (null !== $value) {
                $declarations[] = $property.':'.($this->negative ? '-' : '').$value;
            }
        }
        return implode(';', $declarations);
    }

    public function getChildCombinator() : ?string
    {
        return $this->childCombinator;
    }

    protected function getConfig(string $property, ?string $value = null)
    {
        if (null === $value) {
            return $this->config[$property] ?? null;
        }
        return $this->config[$property][$value] ?? null;
    }

    protected function setDeclaration(string $property, $value) : void
    {
        if (null !== $value && !is_array($value)) {
            $this->declarations[$property] = $value;
        }
    }

    protected function checkCallbacks(string $property, array $args) : ?string
    {
        return $this->callbacks->call($property, $args);
    }

    /**
     * @param array<string> $args
     */
    abstract protected function process(array $args);
}
