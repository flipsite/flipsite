<?php

declare(strict_types=1);

namespace Flipsite\Data;

class InheritedComponentData
{
    public function __construct(private string $appearance, private array $dataSource)
    {
    }

    public function getAppearance(): string
    {
        return $this->appearance;
    }

    public function setAppearance(?string $appearance): void
    {
        if (!$appearance) {
            return;
        }
        $this->appearance = $appearance;
    }

    public function getDataSource(): array
    {
        return $this->dataSource;
    }

    public function addDataSource(array $dataSource): void
    {
        foreach ($dataSource as $key => $value) {
            $this->dataSource[$key] = $value;
        }
    }
}
