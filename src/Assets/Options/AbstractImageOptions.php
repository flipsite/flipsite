<?php

declare(strict_types=1);
namespace Flipsite\Assets\Options;

abstract class AbstractImageOptions
{
    protected const START     = '@';
    protected const DELIMITER = ',';
    protected array $options  = [];
    protected ?float $scale   = null;

    public function __construct($args = null)
    {
        $this->options = $this->defineOptions();
        if (is_string($args)) {
            $pathInfo    = pathinfo($args);
            $spilt       = preg_split('/\.[a-f0-9]{6}$/', $pathInfo['filename']);
            $filename    = explode(self::START, $spilt[0]);
            $pathOptions = explode(self::DELIMITER, $filename[1] ?? '');
            foreach ($pathOptions as $value) {
                foreach ($this->options as $option) {
                    if ($option->parseValue($value)) {
                        continue 2;
                    }
                }
            }
        }
        if (is_array($args)) {
            foreach (array_filter($args) as $option => $value) {
                if (isset($this->options[$option])) {
                    $this->options[$option]->changeValue($value);
                }
            }
        }
    }

    public function __toString() : string
    {
        $options = [];
        foreach ($this->options as $option) {
            $value = $option->getEncoded($this->scale);
            if (is_string($value)) {
                $options[] = $value;
            }
        }
        if (0 === count($options)) {
            return '';
        }
        return self::START.implode(self::DELIMITER, $options);
    }

    public function getValue(string $option)
    {
        if ($this->options[$option]) {
            return $this->options[$option]->getValue();
        }
        return null;
    }

    public function changeScale(?float $scale = null) : void
    {
        $this->scale = $scale;
    }

    abstract protected function defineOptions() : array;
}
