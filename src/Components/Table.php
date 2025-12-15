<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use League\Csv\Reader;
use Flipsite\Utils\ArrayHelper;

final class Table extends AbstractComponent
{
    use Traits\AssetsTrait;
    use Traits\BuilderTrait;
    use Traits\MarkdownTrait;
    use Traits\SiteDataTrait;
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
            if ($collection) {
                $data['td'] = [];
                foreach ($collection->getItems() as $item) {
                    $data['td'][] = array_values($item->getArray());
                }
                if ($data['header'] ?? false) {
                    $data['th'] = [];
                    foreach ($collection->getSchema()->getFields() as $fieldId) {
                        $field        = $collection->getSchema()->getField($fieldId);
                        $data['th'][] = $field->getName();
                    }
                }
            }
            unset($data['collectionId']);
        } elseif (isset($data['td'])) {
            $td = $data['td'];
            if ($data['header'] ?? false) {
                $data['th'] = $td[0];
                $data['td'] = array_slice($td, 1);
            } else {
                $data['td'] = $td;
            }
        }
        if (!isset($data['td'])) {
            $data['th']   = ['Col1', 'Col2', 'Col2'];
            $data['td']   = [];
            $data['td'][] = ['Cell 1,1', '**Cell 2,1**', '*Cell 3,1*'];
            $data['td'][] = ['[Cell 1,2](#)', 'Cell 2,2', 'Cell 3,2'];
            $data['td'][] = ['Cell 1,3', 'Cell 2,3', 'Cell 3,3'];
        }

        return $data;
    }

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data         = $component->getData();
        $style        = $component->getStyle();
        $columnsCount = isset($data['td'][0]) ? count($data['td'][0]) : 0;

        if (($data['th'] ?? false) && count($data['th'])) {
            $thead       = new Element('thead');
            $tr          = new Element('tr');
            $thead       = new Element('thead');
            $tr          = new Element('tr');
            $styleThBase = $style['th'] ?? [];
            if (isset($styleThBase['inherits'])) {
                $styleThBase = ArrayHelper::merge($this->siteData->getSharedStyle($styleThBase['inherits']), $styleThBase);
                unset($styleThBase['inherits']);
            }
            foreach ($data['th'] as $columnIndex => $thCell) {
                $th          = new Element('th');
                $styleTh     = $this->builder->modifyStyle($styleThBase, $data, $columnIndex, $columnsCount);
                $styleTh     = \Flipsite\Utils\StyleAppearanceHelper::apply($styleTh, $inherited->getAppearance());
                if (isset($styleTh['background'])) {
                    $styleTh['background'] = $this->builder->modifyStyle($styleTh['background'] ?? [], $data, $columnIndex, $columnsCount);
                    $styleTh['background'] = \Flipsite\Utils\StyleAppearanceHelper::apply($styleTh['background'], $inherited->getAppearance());
                    $styleTh['background'] = $this->builder->handleBackground($th, $styleTh['background']);
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
            $styleTdBase = $style['td'] ?? [];
            if (isset($styleTdBase['inherits'])) {
                $styleTdBase = ArrayHelper::merge($this->siteData->getSharedStyle($styleTdBase['inherits']), $styleTdBase);
                unset($styleTdBase['inherits']);
            }

            foreach ($data['td'] as $rowIndex => $row) {
                $tr          = new Element('tr');
                $styleTr     = $this->builder->modifyStyle($style['tr'] ?? [], $data, $rowIndex, $rowsCount);
                $styleTr     = \Flipsite\Utils\StyleAppearanceHelper::apply($styleTr, $inherited->getAppearance());
                if (isset($styleTr['background'])) {
                    $styleTr['background'] = $this->builder->modifyStyle($styleTr['background'] ?? [], $data, $rowIndex, $rowsCount);
                    $styleTr['background'] = \Flipsite\Utils\StyleAppearanceHelper::apply($styleTr['background'], $inherited->getAppearance());
                    $styleTr['background'] = $this->builder->handleBackground($tr, $styleTr['background']);
                }
                $tr->addStyle($styleTr);
                foreach ($row as $columnIndex => $cell) {
                    $td      = new Element('td', true);
                    $styleTd = $this->builder->modifyStyle($styleTdBase ?? [], $data, $columnIndex, $columnsCount);
                    $styleTd = \Flipsite\Utils\StyleAppearanceHelper::apply($styleTd, $inherited->getAppearance());
                    if (isset($styleTd['background'])) {
                        $styleTd['background'] = $this->builder->modifyStyle($styleTd['background'] ?? [], $data, $columnIndex, $columnsCount);
                        $styleTd['background'] = \Flipsite\Utils\StyleAppearanceHelper::apply($styleTd['background'], $inherited->getAppearance());
                        $styleTd['background'] = $this->builder->handleBackground($td, $styleTd['background']);
                    }
                    $td->addStyle($styleTd);
                    $html = '';
                    if (is_string($cell)) {
                        $html = $this->getMarkdownLine(trim($cell), ['a', 'strong', 'em', 'code'], $styleTd, $inherited->getAppearance(), $inherited->hasATag(), $data['magicLinks'] ?? false);
                    }
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
