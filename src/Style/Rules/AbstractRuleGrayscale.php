<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleGrayscale extends AbstractRuleFilter
{
    protected string $unit     = '%';
    protected string $function = 'grayscale';
}
