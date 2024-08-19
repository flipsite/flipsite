<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

class RulePerspective extends AbstractRuleTransform
{
    protected array $properties = ['--tw-perspective'];
    protected array $callbacks  = ['size'];
}
