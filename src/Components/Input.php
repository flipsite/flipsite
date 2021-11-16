<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Input extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected bool $oneline = true;
    protected string $tag   = 'input';

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->addStyle($style);
        $type = array_shift($data['flags']);
        if ($this->isType($type)) {
            $this->setAttribute('type', $type);
            $name = $data['name'] ?? $data['flags'][0];
        } else {
            $this->setAttribute('type', 'text');
            $name = $data['name'] ?? $type;
        }
        $this->setAttribute('name', $name);
        $this->setAttribute('id', $name);
        $this->addStyle($style);
    }

    private function isType(string $type) : bool
    {
        return in_array($type, [
            'text',
            'email',
            'tel'
        ]);
    }
}
