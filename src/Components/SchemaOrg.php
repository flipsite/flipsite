<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use Flipsite\Utils\DataHelper;
use Flipsite\Builders\Event;

final class SchemaOrg extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        unset($data['render'], $data['_types']);

        $data = $this->expandRepeat($data);
        $this->builder->dispatch(new Event('schemaorg.graph', $component->getId(), $data));
        $this->render = false;
    }

    private function expandRepeat(array $data): array
    {
        if (isset($data['_repeat'])) {
            $repeat     = $data['_repeat'];
            $collection = $this->siteData->getCollection($repeat, $this->path->getLanguage());
            unset($data['_repeat']);
            if ($collection) {
                $tpl = $data;
                unset($tpl['_options']);
                // Normalize
                $data       = $this->normalizeRepeat($data, $collection->getItemsArray(true));
                $repeatData = $data['_repeatData'] ?? [];
                $data       = [];
                $replaced   = [];
                foreach ($repeatData as $item) {
                    $tplWithData = DataHelper::applyDataToBranch($tpl, $item);
                    unset($tplWithData['_original']);
                    $data[] = $tplWithData;
                }
            }
        }
        if (isset($data['_dataSource'])) {
            $dataSource = $data['_dataSource'];
            unset($data['_dataSource']);
            $tmp        = explode('.', (string)$dataSource, 2);
            $collection = $this->siteData->getCollection($tmp[0], $this->path->getLanguage());
            if ($collection) {
                $itemId = $tmp[1] ?? null;
                $item   = $collection->getItem(intval($itemId));
                if ($item) {
                    $data = DataHelper::applyDataToBranch($data, $item->jsonSerialize());
                    unset($data['_original']);
                }
            }
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->expandRepeat($value);
            }
        }
        return $data;
    }
}
