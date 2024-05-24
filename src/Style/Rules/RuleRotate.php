<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleRotate extends AbstractRuleTransform
{
    protected array $properties = ['--tw-rotate'];
    protected string $unit      = 'deg';
}
