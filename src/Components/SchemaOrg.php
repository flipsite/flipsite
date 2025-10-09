<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use Flipsite\Utils\DataHelper;
use Flipsite\Utils\ArrayHelper;
use Flipsite\Builders\Event;

final class SchemaOrg extends AbstractGroup
{
    use Traits\AssetsTrait;
    use Traits\EnvironmentTrait;
    use Traits\BuilderTrait;
    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        unset($data['render'], $data['_types']);

        $data        = $this->expandRepeat($data, $inherited->getDataSource());
        $assets      = $this->assets; // For use in closure
        $environment = $this->environment; // For use in closure
        $data        = ArrayHelper::applyStringCallback($data, function (string $value) use ($assets, $environment) {
            if (str_starts_with($value, '["') && str_ends_with($value, '"]')) {
                return json_decode($value, true);
            }
            $imageAttributes = $assets->getImageAttributes($value, ['width' => 512]);
            if ($imageAttributes) {
                $value = $environment->getAbsoluteSrc($imageAttributes->getSrc());
            }

            return $value;
        });
        $this->json = $data;
        $this->builder->dispatch(new Event('schemaorg.graph', $data['@type'], $this->json));
        $this->render = false;
    }

    public function getJson(): array
    {
        return $this->json;
    }

    private function expandRepeat(array $data, array $dataSource): array
    {
        if (isset($data['_repeat'])) {
            $repeat     = $data['_repeat'];
            $collection = $this->siteData->getCollection($repeat, $this->path->getLanguage());
            unset($data['_repeat']);
            if ($collection) {
                $tpl = $data;
                unset($tpl['_options']);
                // Normalize
                $repeat     = $collection->getItemsArray(true, $this->environment, $this->siteData, $this->path);
                $data       = $this->normalizeRepeat($data, $repeat);
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
        $replaced = [];
        $data     = DataHelper::applyData($data, $dataSource, $replaced);
        unset($data['_original']);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->expandRepeat($value, $dataSource);
            }
        }
        return $data;
    }
}
