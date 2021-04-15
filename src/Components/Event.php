<?php

declare(strict_types=1);

namespace Flipsite\Components;

class Event
{
    protected string $type;
    protected string $id;
    protected $data;

    public function __construct(string $type, string $id, $data)
    {
        $this->type = $type;
        $this->id   = $id;
        $this->data = $data;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getData()
    {
        return $this->data;
    }
}
