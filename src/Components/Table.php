<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use League\Csv\Reader;

final class Table extends AbstractComponent
{
    use Traits\SiteDataTrait;
    use Traits\MarkdownTrait;
    use Traits\EnvironmentTrait;
    use Traits\AssetsTrait;
    use Traits\NthTrait;
    protected string $tag = 'table';

    public function normalize(string|int|bool|array $data): array
    {
        if (isset($data['file'])) {
            $asset = $this->assets->getAssetSources()->getInfo($data['file']);
            switch ($asset->getExtension()) {
                case 'csv':
                    $data = $this->parseCsv($data, $this->assets->getContents($data['file']));
                    break;
            }
            unset($data['file']);
        } elseif (isset($data['collectionId'])) {
            $collection = $this->siteData->getCollection($data['collectionId']);
            $data['td'] = [];
            foreach ($collection->getItems() as $item) {
                $data['td'][] = $item->getArray();
            }
            if ($data['header'] ?? false) {
                $data['th'] = $collection->getSchema()->getFields();
            }
            unset($data['collectionId']);
        }

        return $data;
    }

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        $style = $component->getStyle();

        if (($data['th'] ?? false) && count($data['th'])) {
            $tr      = new Element('tr');
            foreach ($data['th'] as $i => $col) {
                $th = new Element('th', true);
                $th->addStyle($style['th'] ?? []);
                $th->setContent($col);
                $tr->addChild($th);
            }
            $this->addChild($tr);
        }

        if (($data['td'] ?? false)) {
            foreach ($data['td'] as $i => $row) {
                $tr      = new Element('tr');
                foreach ($row as $cell) {
                    $td = new Element('td', true);
                    $td->addStyle($style['td'] ?? []);
                    $td->setContent($cell);
                    $tr->addChild($td);
                }
                $this->addChild($tr);
            }
        }
    }
    private function parseCsv(array $data, string $csv): array
    {
        $reader = Reader::createFromString($csv);
        $commasCount = substr_count($csv, ',');
        $semicolonCount = substr_count($csv, ';');
        if ($semicolonCount > $commasCount) {
            $reader->setDelimiter(';');
        }
        if ($data['header'] ?? false) {
            $reader->setHeaderOffset(0);
            $data['th'] = $reader->getHeader();
        }
        $records = $reader->getRecords();
        $data['td'] = iterator_to_array($records);
        unset($data['file'], $data['header']);
        return $data;
    }
}
