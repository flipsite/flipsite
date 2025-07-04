<?php

declare(strict_types=1);

namespace Flipsite\Content;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\Localization;

class SchemaField implements \JsonSerializable
{
    private const TYPES = [
        'color',
        'date',
        'enum',
        'file',
        'gallery',
        'gradient',
        'icon',
        'image',
        'list',
        'number',
        'page',
        'published',
        'richtext',
        'slug',
        'svg',
        'text',
        'vcard',
        'video',
    ];

    private string $type;
    private ?string $title                = null;
    private ?string $placeholder          = null;
    private bool $hidden                  = false;
    private ?bool $required               = null;
    private string|bool|null|int $default = null;
    private ?string $options              = null;
    private ?bool $localizable            = null;
    private ?string $json                 = null;

    public function __construct(private string $name, private array $rawField)
    {
        $this->title       = $rawField['_title'] ?? $rawField['_name'] ?? null;
        $this->hidden      = $rawField['_hidden'] ?? false;
        $this->placeholder = $rawField['_placeholder'] ?? null;
        $this->json        = $rawField['json'] ?? null;
        $type              = $rawField['type'];
        $format            = $rawField['format'] ?? null;
        $this->type        = $format ? $format : $type;
        // Backward compatibility
        if ('boolean' === $this->type) {
            $this->type = 'published';
        }
        if ('url' === $this->type) {
            $this->type = 'slug';
        }
        if ('long' === $this->type) {
            $this->type = 'text';
        }
        if ('tel' === $this->type) {
            $this->type = 'text';
        }
        if ('phone' === $this->type) {
            $this->type = 'text';
        }
        if ('email' === $this->type) {
            $this->type = 'text';
        }
        if (!in_array($this->type, self::TYPES)) {
            throw new \Exception('Invalid field type ('.$this->type.')');
        }
        $this->default  = $rawField['default'] ?? null;
        if ('number' === $this->type) {
            $this->default = (int) $this->default;
        }
        $this->required = $rawField['required'] ?? null;
        if ('enum' === $this->type) {
            if ($rawField['options']) {
                $this->options = is_array($rawField['options']) ? json_encode($rawField['options']) : $rawField['options'];
            }
            if (!$this->default) {
                $options       = ArrayHelper::decodeJsonOrCsv($this->options);
                $this->default = $options[0] ?? null;
            }
            if (is_array($this->options)) {
                $this->options = array_map('trim', $this->options);
            }
        }
        if (in_array($this->type, ['enum', 'published'])) {
            $this->required = true;
        }

        $this->localizable = $rawField['localizable'] ?? false;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->title ?? ucfirst($this->name);
    }

    public function appendDelta(array $delta)
    {

        if (array_key_exists('type', $delta)) {
            $this->type = $delta['type'];
            if (!in_array($this->type, self::TYPES)) {
                throw new \Exception('Invalid field type '.$this->type);
            }
        }
        if (array_key_exists('default', $delta)) {
            $this->default = $delta['default'];
            if ('number' === $this->type) {
                $this->default = (int) $this->default;
            }
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
        if (array_key_exists('_title', $delta)) {
            $this->title = $delta['_title'];
        }
        if (array_key_exists('_placeholder', $delta)) {
            $this->placeholder = $delta['_placeholder'];
        }
        if (array_key_exists('_hidden', $delta)) {
            $this->hidden = !!$delta['_hidden'];
        }
        if (array_key_exists('json', $delta)) {
            $this->json = $delta['json'];
        }
    }

    public function getDefault(): null|string|bool|int
    {
        if (null === $this->default && 'enum' === $this->type) {
            $options = ArrayHelper::decodeJsonOrCsv($this->options);
            return $options[0] ?? $this->default;
        }
        return $this->default;
    }

    public function isRequired(): bool
    {
        return $this->required ?? false;
    }

    public function getJson(): array
    {
        return $this->json ? json_decode($this->json, true) : [];
    }

    public function validate(string|bool $value): string|bool
    {
        if (is_string($value) && !$this->localizable && Localization::isLocalization($value)) {
            $localization = new Localization([], $value);
            return $localization->getValue();
        }
        if ('richtext' === $this->type) {
            return $this->validateRichtext($value);
        }
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

    private function validateRichtext(string $value): string
    {
        if (!$value) {
            return '[]';
        }
        $json = json_decode($value, true);
        if (!$json) {
            $json = \Flipsite\Utils\RichtextHelper::fallbackFromString($value);
        }
        foreach ($json as &$item) {
            if (isset($item['data'])) {
                if (is_array($item['data']) && ArrayHelper::isAssociative($item['data'])) {
                    foreach ($item['data'] as $key => $val) {
                        $item[$key] = $val;
                    }
                } else {
                    $item['value'] = $item['data'];
                }
                unset($item['data']);
            }
        }

        return json_encode($json, \JSON_UNESCAPED_UNICODE);
    }

    public function jsonSerialize(): mixed
    {
        $json = [
            'type' => $this->type,
            'id'   => $this->name
        ];
        if ($this->title) {
            $json['_title'] = $this->title;
        }
        $json['_hidden'] = $this->hidden ?? false;
        if ($this->default) {
            $json['default'] = $this->default;
        }
        if ($this->placeholder) {
            $json['_placeholder'] = $this->placeholder;
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
        if ($this->json) {
            $json['json'] = $this->json;
        }
        return $json;
    }
}
