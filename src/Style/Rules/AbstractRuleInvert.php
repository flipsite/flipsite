<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleInvert extends AbstractRuleFilter
{
    protected string $unit     = '%';
    protected string $function = 'invert';
}
