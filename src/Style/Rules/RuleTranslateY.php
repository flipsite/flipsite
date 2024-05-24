<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleTranslateY extends AbstractRuleTransform
{
    protected array $properties = ['--tw-translate-y'];
    protected array $callbacks  = ['size'];
}
