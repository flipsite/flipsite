<?php

declare(strict_types=1);
namespace Flipsite\Data;

abstract class AbstractComponentData
{
    /**
     * @param null|int|string[] $path
     */
    protected ?array $path;
    protected null|int|string $id;
    protected string $type;
    protected array $data     = [];
    protected array $style    = [];
    protected array $meta     = [];

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

    public function getParentId(): null|int|string
    {
        if (!$this->path || count($this->path) < 2) {
            return null;
        }
        return $this->path[count($this->path) - 2];
    }

    public function getPath(): ?array
    {
        return $this->path;
    }

    public function getId(): null|int|string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(bool $flatten = false): array
    {
        if ($flatten) {
            $dot = new \Adbar\Dot($this->data);
            return $dot->flatten();
        }
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
        if ($flatten) {
            $dot = new \Adbar\Dot($this->style);
            return $dot->flatten();
        }
        return $this->style;
    }

    public function setStyle(array $style): void
    {
        $this->style = $style;
    }

    public function addStyle(array $style): void
    {
        $this->style = array_merge($this->style, $style);
    }

    public function getStyleValue(string $setting, bool $remove = null): ?string
    {
        $value = $this->style[$setting] ?? null;
        if ($remove) {
            unset($this->style[$setting]);
        }
        return $value;
    }

    public function setStyleValue(string $setting, string|array|bool|int $value): void
    {
        $this->style[$setting] = $value;
    }

    public function removeStyleValue(string $setting): void
    {
        unset($this->style[$setting]);
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(AbstractComponentData $child)
    {
        return $this->children[] = $child;
    }

    public function purgeChildren(): void
    {
        $this->children = [];
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMetaValue(string $attribute, string|array|bool|int $value): void
    {
        $this->meta[$attribute] = $value;
    }

    public function getMetaValue(string $attribute): null|string|array|bool|int
    {
        return $this->meta[$attribute] ?? null;
    }
}
