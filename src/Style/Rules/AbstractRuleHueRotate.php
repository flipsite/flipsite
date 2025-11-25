<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleHueRotate extends AbstractRuleFilter
{
    protected string $unit     = 'deg';
    protected string $function = 'hue-rotate';
}
