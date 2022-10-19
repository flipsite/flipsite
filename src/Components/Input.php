<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Input extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected bool $oneline = true;
    protected bool $empty   = true;
    protected string $tag   = 'input';

    public function build(array $data, array $style, string $appearance): void
    {
        $this->addStyle($style);
        $this->setAttribute('type', $data['type'] ?? 'text');
        unset($data['type']);
        unset($data['flags']);
        foreach ($data as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }
}
