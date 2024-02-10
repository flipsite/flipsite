<?php

declare(strict_types=1);
namespace Flipsite\Content;

class Schema implements \JsonSerializable
{
    private array $fields = [];

    public function __construct(private array $rawSchema)
    {
        foreach ($this->rawSchema as $field => $rawField) {
            $this->fields[$field] = new SchemaField($field, $rawField);
        }
    }

    public function getSlugField() : ?string {
        foreach ($this->fields as $fieldId => $val) {
            if ('slug' === ($val->getType() ?? '')) {
                return $fieldId;
            }
        }
        return null;
    }

    public function hasField(string $fieldId) : bool
    {
        return array_key_exists($fieldId, $this->fields);
    }

    public function addField(array $rawField) {
        $field = $rawField['name'];
        unset($rawField['name']);
        $this->fields[$field] = new SchemaField($field, $rawField);
    }

    public function editField(string $fieldId, array $delta) : ?string {
        $newName = $delta['name'] ?? null;
        unset($delta['name']);
        $this->fields[$fieldId]->appendDelta($delta);
        if ($newName) {
            $this->fields[$newName] = $this->fields[$fieldId];
            unset($this->fields[$fieldId]);
        }
        return $newName;
    }

    public function getField(string $field) : ?SchemaField
    {
        return $this->fields[$field] ?? null;
    }

    public function hasPublishedField() : bool
    {
        foreach ($this->fields as $field) {
            if ('published' === $field->getType()) {
                return true;
            }
        }
        return false;
    }

    public function validate(array $rawData) : array
    {
        $data = [];
        foreach ($this->fields as $field => $schemaField) {
            if (!array_key_exists($field, $rawData)) {
                $data[$field] = $schemaField->getDefault();
            } elseif ($rawData[$field] !== null) {
                $data[$field] = $schemaField->validate($rawData[$field]);
            }
        }
        return $data;
    }

    public function jsonSerialize(): mixed
    {
        return $this->fields;
    }
}
