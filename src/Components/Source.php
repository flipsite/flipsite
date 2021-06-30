<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Source extends AbstractElement
{
    protected string $tag   = 'source';
    protected array $srcset = [];
    protected array $sizes  = [];

    public function __construct(string $type)
    {
        $this->empty = true;
        $this->setAttribute('type', $type);
    }

    public function addSrcset(?string $variant, string $src) : void
    {
        if (null !== $variant) {
            $src .= ' '.$variant;
        }
        $this->srcset[] = $src;
    }

    public function addSize(string $size) : void
    {
        $this->sizes[] = $size;
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        if (count($this->sizes)) {
            $this->setAttribute('sizes', implode(', ', $this->sizes));
        }
        $length = 22 + mb_strlen($this->attributes['type']);
        $spaces = str_repeat(' ', ($level + 1) * $indentation + $length - 8);
        $this->setAttribute("\n".$spaces.'srcset', implode(",\n".$spaces, $this->srcset));
        return parent::render($indentation, $level, $oneline);
    }
}
