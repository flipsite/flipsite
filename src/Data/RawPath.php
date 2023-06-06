<?php

declare(strict_types=1);
namespace Flipsite\Data;

class RawPath
{
    private bool $hasParams;
    private string $content;
    private string $key;

    public function __construct(private string $rawPath)
    {
        $this->hasParams = strpos($rawPath, ':') !== false;
        if (!$this->hasParams) {
            return;
        }
        $tmp = explode('/', $this->rawPath);
        $tmp = array_values(array_filter($tmp, function($item) {
            return strpos($item, ':') !== false;
        }));
        $tmp = trim(trim(array_shift($tmp),']'),':');
        $tmp = explode('[',$tmp);
        $this->content = $tmp[0];
        $this->key = $tmp[1];
    }

    public function hasParams() : bool
    {
        return $this->hasParams;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function getPage(array $dataItem): string
    {
        $replaceWith = $dataItem[$this->key];
        return str_replace(':'.$this->content.'['.$this->key.']', $replaceWith, $this->rawPath);
    }
}
