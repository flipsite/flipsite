<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Input extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected bool $oneline = true;
    protected bool $empty   = true;
    protected string $tag   = 'input';

    public function build(array $data, array $style, arrat $options): void
    {
        $this->addStyle($style);
        $this->setAttribute('type', $data['type'] ?? 'text');
        unset($data['type'], $data['flags']);
        if (!isset($data['id']) && isset($data['name'])) {
            $this->setAttribute('id', $data['name']);
        }
        foreach ($data as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }
}
