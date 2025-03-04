<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use League\Csv\Reader;

final class Table extends AbstractComponent
{
    use Traits\AssetsTrait;
    use Traits\BuilderTrait;
    use Traits\MarkdownTrait;
    use Traits\ClassesTrait;
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
        $data         = $component->getData();
        $style        = $component->getStyle();
        $columnsCount = count($data['td'][0]);

        if (($data['th'] ?? false) && count($data['th'])) {
            $thead = new Element('thead');
            $tr    = new Element('tr');
            foreach ($data['th'] as $columnIndex => $thCell) {
                $th          = new Element('th');
                $styleTh     = $this->builder->optimizeStyle($style['th'] ?? [], $columnIndex, $columnsCount);
                if (isset($styleTh['background'])) {
                    $this->builder->handleBackground($tr, $styleTh['background']);
                }
                $th->addStyle($styleTh);
                $th->setContent($thCell);
                $tr->addChild($th);
            }
            $thead->addChild($tr);
            $this->addChild($thead);
        }

        if (($data['td'] ?? false)) {
            $tbody       = new Element('tbody');
            $rowsCount   = count($data['td']);
            foreach ($data['td'] as $rowIndex => $row) {
                $tr          = new Element('tr');
                $styleTr     = $this->builder->optimizeStyle($style['tr'] ?? [], $rowIndex, $rowsCount);
                if (isset($styleTr['background'])) {
                    $this->builder->handleBackground($tr, $styleTr['background']);
                }
                $tr->addStyle($styleTr);
                foreach ($row as $columnIndex => $cell) {
                    $td = new Element('td', true);

                    $styleTd     = $this->builder->optimizeStyle($style['td'] ?? [], $columnIndex, $columnsCount);
                    if (isset($styleTd['background'])) {
                        $this->builder->handleBackground($td, $styleTd['background']);
                    }
                    $td->addStyle($styleTd);
                    $html  = $this->getMarkdownLine(trim($cell));
                    $html  = $this->addClassesToHtml($html, ['a', 'strong', 'em', 'code'], $component->getStyle(), $inherited->getAppearance());
                    $td->setContent($html);
                    $tr->addChild($td);
                }
                $this->addChild($tr);
            }
            $this->addChild($tbody);
        }
    }

    private function parseCsv(array $data, string $csv): array
    {
        $reader         = Reader::createFromString($csv);
        $commasCount    = substr_count($csv, ',');
        $semicolonCount = substr_count($csv, ';');
        if ($semicolonCount > $commasCount) {
            $reader->setDelimiter(';');
        }
        $records    = $reader->getRecords();
        $data['td'] = iterator_to_array($records);

        if ($data['header'] ?? false) {
            $data['th'] = $data['td'][0];
            $data['td'] = array_slice($data['td'], 1);
        }

        unset($data['file'], $data['header']);
        return $data;
    }
}
