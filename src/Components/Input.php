<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Input extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected bool $oneline = true;
    protected string $tag   = 'input';

    public function with(ComponentData $data) : void
    {
        $flags = $data->getFlags();
        $type  = array_shift($flags);
        if ($this->isType($type)) {
            $this->setAttribute('type', $type);
            $name = $data->get('name', true) ?? $flags[0];
        } else {
            $this->setAttribute('type', 'text');
            $name = $data->get('name', true) ?? $type;
        }
        $this->setAttribute('name', $name);
        $this->setAttribute('id', $name);
        $this->addStyle($data->getStyle());
        $this->setAttributes($data->get());
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
