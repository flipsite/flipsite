<?php

declare(strict_types=1);

namespace Flipsite\Data;

use Ckr\Util\ArrayMerger;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractData
{
    /**
     * @var array<string,mixed>
     */
    protected array $data;

    public function appendFile(string $filename) : void
    {
        $data = Yaml::parse(file_get_contents($filename));
        $this->appendData($data);
    }

    public function appendData(array $data) : void
    {
        $this->data = ArrayMerger::doMerge($data, $this->data);
    }

    public function getRaw() : array
    {
        return $this->data;
    }

    public function getAttributes(string $section) : array
    {
        return array_keys($this->data[$section]);
    }

    public function getValue(string $section, $attribute = null)
    {
        if (null === $attribute) {
            return $this->data[$section] ?? null;
        }
        if (is_string($attribute)) {
            return $this->data[$section][$attribute] ?? null;
        }
        if (is_array($attribute)) {
            $key = array_shift($attribute);
            if (!isset($this->data[$section][$key])) {
                return null;
            }
            $value = $this->data[$section][$key];
            while (count($attribute) && is_array($value)) {
                $key = array_shift($attribute);
                if (!isset($value[$key])) {
                    return $value;
                }
                $value = $value[$key];
            }
            return $value;
        }
    }

    public function isSet(string $section, ?string $attribute = null) : bool
    {
        if (null === $attribute) {
            return isset($this->data[$section]);
        }
        return isset($this->data[$section][$attribute]);
    }
}
