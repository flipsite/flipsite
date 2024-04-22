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

    protected ?string $pseudoElement = null;

    protected int $order = 100;

    protected array $config;

    protected array $themeSettings;

    private CallbackInterface $callbacks;

    public function __construct(array $args, bool $negative, array &$config, array &$themeSettings, CallbackInterface $callbacks = null)
    {
        $this->negative      = $negative;
        $this->config        = $config;
        $this->themeSettings = $themeSettings;
        $this->callbacks     = $callbacks;
        $this->process($args);
    }

    public function getDeclarations(): string
    {
        $declarations = [];
        foreach ($this->declarations as $property => $value) {
            if (null !== $value) {
                $declarations[] = $property.':'.($this->negative ? '-' : '').$value;
            }
        }
        return implode(';', $declarations);
    }

    public function getOrder(): int
    {
        return $this->order;
    }
    public function getChildCombinator(): ?string
    {
        return $this->childCombinator;
    }

    public function getPseudoElement(): ?string
    {
        return $this->pseudoElement;
    }

    protected function getConfig(string $property, ?string $value = null)
    {
        if (null === $value) {
            return $this->config[$property] ?? null;
        }
        return $this->config[$property][$value] ?? null;
    }

    protected function setDeclaration(string $property, $value): void
    {
        if (null !== $value && !is_array($value)) {
            $this->declarations[$property] = $value;
        }
    }

    protected function checkCallbacks(string $property, array $args, ?array $options = null): ?string
    {
        return $this->callbacks->call($property, $args, $options);
    }

    /**
     * @param array<string> $args
     */
    abstract protected function process(array $args);
}
