<?php

declare(strict_types=1);
namespace Flipsite\Data;

class InheritedComponentData
{
    private ?string $navState             = null;
    private string|int|null $parentId     = null;
    private string|null $parentType       = null;

    private string|int|null $pageCollectionId = null;
    private string|int|null $pageItemId       = null;

    private string|int|null $collectionId = null;
    private string|int|null $itemId       = null;

    public function __construct(private string $appearance, private array $dataSource = [])
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

    public function getNavState(): ?string
    {
        return $this->navState;
    }

    public function setNavState(?string $navState = null): void
    {
        $this->navState = $navState;
    }

    public function setParent(string|int $parentId, string $parentType): void
    {
        $this->parentId   = $parentId;
        $this->parentType = $parentType;
    }

    public function getParentId(): string|int|null
    {
        return $this->parentId;
    }

    public function getParentType(): string|null
    {
        return $this->parentType;
    }

    public function setPageItem(string|int $collectionId, string|int $itemId): void
    {
        $this->pageCollectionId = $collectionId;
        $this->pageItemId       = $itemId;
    }

    public function getPageCollectionId(): string|int|null
    {
        return $this->pageCollectionId;
    }

    public function getPageItemId(): string|int|null
    {
        return $this->pageItemId;
    }

    public function setRepeatItem(string|int $collectionId, string|int $itemId): void
    {
        $this->collectionId = $collectionId;
        $this->itemId       = $itemId;
    }

    public function getCollectionId(): string|int|null
    {
        return $this->collectionId;
    }

    public function getItemId(): string|int|null
    {
        return $this->itemId;
    }
}
