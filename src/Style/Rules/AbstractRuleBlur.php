<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleBlur extends AbstractRuleFilter
{
    protected string $unit     = 'px';
    protected string $function = 'blur';
}
