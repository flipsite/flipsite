<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScaleX extends AbstractRuleTransform
{
    protected array $properties = ['--tw-scale-x'];
    protected string $unit      = '%';
}
