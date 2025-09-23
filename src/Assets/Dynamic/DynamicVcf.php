<?php

declare(strict_types=1);
namespace Flipsite\Assets\Dynamic;

use Flipsite\Data\SiteDataInterface;
use Flipsite\Content\SchemaField;
use Flipsite\Content\Item;
use Flipsite\Assets\Sources\AssetSourcesInterface;
use Intervention\Image\ImageManager;

class DynamicVcf implements DynamicAssetsInterface
{
    public function __construct(private SiteDataInterface $siteData, private AssetSourcesInterface $assetSources)
    {
    }

    public function isAsset(string $asset): bool
    {
        $collectionIds = $this->siteData->getCollectionIds();
        foreach ($collectionIds as $collectionId) {
            $collection = $this->siteData->getCollection($collectionId);
            $vCardField = $collection->getSchema()->getFieldsOfType('vcard')[0] ?? null;
            if (!$vCardField) {
                continue;
            }
            foreach ($collection->getItems() as $item) {
                if ($item->get($vCardField) === $asset) {
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
            $collection = $this->siteData->getCollection($collectionId);
            $vCardField = $collection->getSchema()->getFieldsOfType('vcard')[0] ?? null;
            if (!$vCardField) {
                continue;
            }
            foreach ($collection->getItems() as $item) {
                if ($item->get($vCardField) === $asset) {
                    return $this->createVcard($collection->getSchema()->getField($vCardField), $item);
                }
            }
        }
        return '';
    }

    public function getMimetype(string $asset): string
    {
        return 'text/vcard';
    }

    private function createVcard(SchemaField $field, Item $item): string
    {
        $vcard                          = [];
        $replaced                       = [];
        $dataSource                     = $item->getArray();
        $dataSource['site.name']        = $this->siteData->getName();
        $dataSource['site.description'] = $this->siteData->getDescription();
        foreach ($this->siteData->getGlobalVars() as $key => $value) {
            $dataSource[$key] = $value;
        }
        $vCardData = \Flipsite\Utils\DataHelper::applyData($field->getJson(), $dataSource, $replaced);
        unset($vCardData['_original']);

        $name       = explode(' ', $vCardData['name'] ?? ' ');
        $vcard['N'] = [$name[1] ?? '', $name[0] ?? '', '', '', ''];
        if (isset($vCardData['phone'])) {
            $vcard['TEL'] = $vCardData['phone'];
        }
        if (isset($vCardData['mobile'])) {
            $vcard['TEL;TYPE=CELL'] = $vCardData['mobile'];
        }
        if (isset($vCardData['email'])) {
            $vcard['EMAIL'] = $vCardData['email'];
        }
        if (isset($vCardData['company'])) {
            $vcard['ORG'] = $vCardData['company'];
        }
        if (isset($vCardData['url'])) {
            $vcard['URL'] = $vCardData['url'];
        }

        $address = [
            '',
            '',
            $vCardData['street'] ?? '',
            $vCardData['city'] ?? '',
            $vCardData['state'] ?? '',
            $vCardData['zip'] ?? '',
            $vCardData['country'] ?? '',
        ];
        if (implode('', $address) !== '') {
            $vcard['ADR'] = implode(';', $address);
        }

        $asset = $this->assetSources->getInfo($vCardData['photo'] ?? '');
        if ($asset) {
            $manager    = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image      = $manager->read($asset->getContents());
            $image->fit(320, 320);
            $encoded                             = $image->toJpeg(90);
            $base64                              = base64_encode((string)$encoded);
            $vcard['PHOTO;ENCODING=b;TYPE=JPEG'] = $base64;
        }

        $vcard['PRODID'] = '-//FlipSite//FlipSite v1.0//EN';
        $encoded         = "BEGIN:VCARD\nVERSION:3.0\n";
        foreach ($vcard as $key => $value) {
            $line = '';
            if (is_array($value)) {
                $line .= $key.':' . implode(';', $value);
            } else {
                $line .= $key.':'.$value;
            }
            $encoded .= trim(wordwrap($line, 73, "\n ", true)). "\n";
        }
        $encoded .= "END:VCARD\n";
        return $encoded;
    }
}
