<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;
use Flipsite\Utils\DataHelper;
use Flipsite\Builders\Event;

final class SchemaOrg extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\SiteDataTrait;
    use Traits\PathTrait;

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        unset($data['render']);
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
                $tpl      = $data;
                $data     = [];
                $replaced = [];
                foreach ($collection->getItemsArray(true) as $item) {
                    $tplWithData = DataHelper::applyDataToBranch($tpl, $item);
                    unset($tplWithData['_original']);
                    $data[] = $tplWithData;
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
