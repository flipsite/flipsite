<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class Filter
{
    public function __construct(private string $type = 'or', private ?string $filter = null, private ?string $pattern = null)
    {
    }

    public function filterValue(string|null $value): bool
    {
        if (!$value) {
            $value = null;
        }
        if ('notEmpty' === $this->type) {
            return !!$value;
        }
        if ('empty' === $this->type) {
            return !$value;
        }
        if ($value && $this->pattern) {
            return preg_match('/'.$this->pattern.'/', $value);
        }

        $filter = ArrayHelper::decodeJsonOrCsv($this->filter);
        $value  = ArrayHelper::decodeJsonOrCsv($value);

        if ('or' === $this->type) {
            foreach ($value as $v) {
                if (in_array($v, $filter)) {
                    return true;
                }
            }
        }

        if ('not' === $this->type) {
            foreach ($value as $v) {
                if (in_array($v, $filter)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    public function filterList(array $list, string $field): array
    {
        return array_filter($list, function ($item) use ($field) {
            return $this->filterValue($item[$field] ?? null);
        });
    }
}
