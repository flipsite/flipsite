<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class Filter
{
    public function __construct(private string $type = 'or', private ?string $filter = null, private ?string $pattern = null)
    {
    }
    public function filterValue(string|bool|null $value): bool
    {
        return true;
    }
    public function filterList(array $list, string $field): array
    {
        return array_filter($list, function ($item) use ($field) {
            return $this->filterValue($item[$field]);
        });
    }
}
