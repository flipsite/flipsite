<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RulePr extends AbstractRuleSpacing
{
    /**
     * @var array<string>
     */
    protected array $properties = ['padding-right'];

    protected ?array $safeAreaInset = ['safe-area-inset-right'];
}
