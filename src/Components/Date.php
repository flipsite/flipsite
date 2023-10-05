<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Date extends AbstractComponent
{
    protected string $tag  = 'time';

    public function build(array $data, array $style, array $options) : void
    {
        $this->addStyle($style);
        $timestamp = strtotime($data['value']);
        $this->setContent(date($data['format'] ?? 'Y-m-d', $timestamp));
    }
}
