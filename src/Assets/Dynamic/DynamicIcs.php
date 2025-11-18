<?php

declare(strict_types=1);
namespace Flipsite\Assets\Dynamic;

use Flipsite\Data\SiteDataInterface;
use Flipsite\Content\SchemaField;
use Flipsite\Content\Item;
use Flipsite\Assets\Sources\AssetSourcesInterface;

class DynamicIcs implements DynamicAssetsInterface
{
    public function __construct(private SiteDataInterface $siteData, private AssetSourcesInterface $assetSources)
    {
    }

    public function isAsset(string $asset): bool
    {
        $collectionIds = $this->siteData->getCollectionIds();
        foreach ($collectionIds as $collectionId) {
            $collection     = $this->siteData->getCollection($collectionId);
            $iCalendarField = $collection->getSchema()->getFieldsOfType('icalendar')[0] ?? null;
            if (!$iCalendarField) {
                continue;
            }
            foreach ($collection->getItems() as $item) {
                if ($item->get($iCalendarField) === $asset) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getContents(string $asset): string
    {
        $collectionIds = $this->siteData->getCollectionIds();
        foreach ($collectionIds as $collectionId) {
            $collection     = $this->siteData->getCollection($collectionId);
            $iCalendarField = $collection->getSchema()->getFieldsOfType('icalendar')[0] ?? null;
            if (!$iCalendarField) {
                continue;
            }
            foreach ($collection->getItems() as $item) {
                if ($item->get($iCalendarField) === $asset) {
                    return $this->createIcalendar($collection->getSchema()->getField($iCalendarField), $item);
                }
            }
        }
        return '';
    }

    public function getMimetype(string $asset): string
    {
        return 'text/calendar';
    }

    private function createIcalendar(SchemaField $field, Item $item): string
    {
        $iCalendar                          = [];
        $replaced                           = [];
        $dataSource                         = $item->getArray();
        $dataSource['site.name']            = $this->siteData->getName();
        $dataSource['site.description']     = $this->siteData->getDescription();
        foreach ($this->siteData->getGlobalVars() as $key => $value) {
            $dataSource[$key] = $value;
        }
        $iCalendarData = \Flipsite\Utils\DataHelper::applyData($field->getJson(), $dataSource, $replaced);
        unset($iCalendarData['_original']);

        foreach ($iCalendar as $attr => &$value) {
            if (is_string($value) && \Flipsite\Utils\Localization::isLocalization($value)) {
                $loc   = new \Flipsite\Utils\Localization($this->siteData->getLanguages(), $value);
                $value = $loc->getValue();
            }
        }

        // TODO

        $encoded = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//FlipSite//FlipSite v1.0//EN\nBEGIN:VEVENT\n";
        foreach ($iCalendar as $key => $value) {
            $line = '';
            if (is_array($value)) {
                $line .= $key.':' . implode(';', $value);
            } else {
                $line .= $key.':'.$value;
            }
            $encoded .= trim(wordwrap($line, 73, "\n ", true)). "\n";
        }
        $encoded .= "END:VEVENT\n";
        $encoded .= "END:VCALENDAR\n";
        return $encoded;
    }
}
