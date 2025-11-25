<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleSepia extends AbstractRuleFilter
{
    protected string $unit     = '%';
    protected string $function = 'sepia';
}
