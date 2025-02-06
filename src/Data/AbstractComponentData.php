<?php

declare(strict_types=1);

namespace Flipsite\Data;

abstract class AbstractComponentData
{
    protected int|string $id;
    protected string $type;
    protected array $data     = [];
    protected array $style    = [];

    /**
     * @var array<AbstractComponentData>
     */
    protected array $children = [];

    // Recursive deep clone
    public function __clone()
    {
        foreach ($this->children as $key => $child) {
            $this->children[$key] = clone $child;  // Recursively clone child nodes
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        switch ($this->type) {
            case 'button':
            case 'container':
                return 'group';
        }
        return $this->type;
    }

    public function getData(bool $flatten = false): array
    {
        return $this->data;
    }

    public function getDataValue(string $attribute, bool $remove = null): array|string|bool|int|null
    {
        $value = $this->data[$attribute] ?? null;
        if ($remove) {
            unset($this->data[$attribute]);
        }
        return $value;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function setDataValue(string $attribute, string|array|bool|int $value): void
    {
        $this->data[$attribute] = $value;
    }

    public function getStyle(bool $flatten = false): array
    {
        return $this->style;
    }

    public function setStyle(array $style): void
    {
        $this->style = $style;
    }

    public function getStyleValue(string $setting, bool $remove = null): ?string
    {
        $value = $this->style[$setting] ?? null;
        if ($remove) {
            unset($this->style[$setting]);
        }
        return $value;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}
