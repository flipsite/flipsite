<?php

declare(strict_types=1);
namespace Flipsite\Content;

use Flipsite\Utils\ArrayHelper;

class Item implements \JsonSerializable
{
    private int $id;
    private array $data;

    public function __construct(private Schema $schema, private array $rawData)
    {
        $this->id   = intval($rawData['_id']);
        $this->data = $schema->validate($rawData);
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function renameField(string $oldName, string $newName)
    {
        if (array_key_exists($oldName, $this->data)) {
            $this->data[$newName] = $this->data[$oldName];
            unset($this->data[$oldName]);
        }
    }

    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function applyDelta(array $delta, bool $setDefault = false)
    {
        $dataWithDelta = ArrayHelper::merge($this->data, $delta);
        $this->data    = $this->schema->validate($dataWithDelta, $setDefault);
    }

    public function get(string $field) : mixed
    {
        return $this->data[$field] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return array_merge(['_id' => $this->id], $this->data);
    }
}
