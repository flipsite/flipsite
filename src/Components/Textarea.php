<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Textarea extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected bool $oneline = true;
    protected string $tag   = 'textarea';

    public function build(array $data, array $style, string $appearance): void
    {
        $this->addStyle($style);
        unset($data['flags']);
        if (isset($data['value'])) {
            $this->setContent($data['value']);
            unset($data['value']);
        }
        if (!isset($data['id']) && isset($data['name'])) {
            $this->setAttribute('id', $data['name']);
        }
        foreach ($data as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }
}
