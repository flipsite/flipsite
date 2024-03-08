<?php

declare(strict_types=1);
namespace Flipsite\Content;

use \Flipsite\Utils\ArrayHelper;
class SchemaField implements \JsonSerializable
{
    private const TYPES = [
        'date',
        'email',
        'enum',
        'gallery',
        'icon',
        'image',
        'list',
        'long',
        'phone',
        'published',
        'richtext',
        'slug',
        'svg',
        'text',
    ];

    private string $type;
    private ?bool $required  = null;
    private ?string $default = null;
    private ?string $options = null;
    private ?bool $localizable = null;

    public function __construct(private string $id, private array $rawField)
    {
        $type       = $rawField['type'];
        $format     = $rawField['format'] ?? null;
        $this->type = $format ? $format : $type;
        // Backward compatibility
        if ('boolean' === $this->type) {
            $this->type = 'published';
        }
        if ('markdown' === $this->type) {
            $this->type = 'richtext';
        }
        if ('url' === $this->type) {
            $this->type = 'slug';
        }
        if ('tel' === $this->type) {
            $this->type = 'phone';
        }
        if (!in_array($this->type, self::TYPES)) {
            throw new \Exception('Invalid field type '.$this->type);
        }
        $this->default = $rawField['default'] ?? null;
        $this->required = $rawField['required'] ?? null;
        if ('enum' === $this->type) {
            if ($rawField['options']) {
                $this->options = is_array($rawField['options']) ? json_encode($rawField['options']) : $rawField['options'];
            }
            if (!$this->default) {
                $options = ArrayHelper::decodeJsonOrCsv($this->options);
                $this->default = $options[0] ?? null;
            }
        }
        if (in_array($this->type, ['enum', 'published'])) {
            $this->required = true;
        }

        $this->localizable = $rawField['localizable'] ?? false;
    }
    public function getType() : string {
        return $this->type;
    }

    public function appendDelta(array $delta) {
        if (array_key_exists('type', $delta)) {
            $this->type = $delta['type'];
            if (!in_array($this->type, self::TYPES)) {
                throw new \Exception('Invalid field type '.$this->type);
            }
        }
        if (array_key_exists('default', $delta)) {
            $this->default = $delta['default'];
        }
        if (array_key_exists('required', $delta)) {
            $this->required = !!$delta['required'];
        }
        if (array_key_exists('options', $delta)) {
            $this->options = $delta['options'];
        }
        if (array_key_exists('localizable', $delta)) {
            $this->localizable = !!$delta['localizable'];
        }
    }
    public function getDefault() : null|string|bool {
        if (null === $this->default && 'enum' === $this->type) {    
            $options = ArrayHelper::decodeJsonOrCsv($this->options);
            return $options[0] ?? $this->default;
        }
        return $this->default;
    }
    public function validate(string|bool $value) : string|bool
    {
        if ('published' === $this->type) {
            return (bool) $value;
        }
        if ('enum' === $this->type) {
            $options = ArrayHelper::decodeJsonOrCsv($this->options);
            if (!is_string($value) || !in_array($value, $options)) {
                return $options[0];
            }
        }
        return $value;
    }

    public function jsonSerialize(): mixed
    {
        $json = [
            'type' => $this->type,
        ];
        if ($this->default) {
            $json['default'] = $this->default;
        }
        if ($this->required) {
            $json['required'] = $this->required;
        }
        if ($this->options) {
            $json['options'] = $this->options;
        }
        if ($this->localizable) {
            $json['localizable'] = $this->localizable;
        }
        return $json;
    }
}
