<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class AbstractType
{
    protected ?string $mediaQuery = null;
    protected string $prefix;
    protected ?string $pseudo = null;
    protected string $parent  = '';
    protected int $order;

    public function getMediaQuery() : ?string
    {
        return $this->mediaQuery;
    }

    public function getPrefix() : string
    {
        return $this->prefix;
    }

    public function getParent() : string
    {
        return mb_strlen($this->parent) ? $this->parent.' ' : '';
    }

    public function getPseudo() : ?string
    {
        return $this->pseudo;
    }

    public function order() : int
    {
        return $this->order;
    }
}
