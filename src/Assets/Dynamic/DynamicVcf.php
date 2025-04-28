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
        $vcard   = [];

        // Name
        // $name       = explode(' ', $item->get('name'));
        // $vcard['N'] = [$name[1], $name[0], '', '', ''];
        // if ($item->get('phone')) {
        //     $vcard['TEL'] = $item->get('phone');
        // }
        // if ($item->get('email')) {
        //     $vcard['EMAIL'] = $item->get('email');
        // }
        // $global = $this->siteData->getGlobalVars();
        // if (isset($global['legal.company_name'])) {
        //     $vcard['ORG'] = $global['legal.company_name'];
        // }
        // $vcard['URL'] = 'https://flipsite.io/';

        // $street  = 'Talldungevägen 30';
        // $city    = 'Gottby';
        // $state   = '';
        // $zip     = '22130';
        // $country = 'Finland';

        // $vcard['ADR'] = ';;'.$street.';'.$city.';'.$state.';'.$zip.';'.$country;
        // $asset        = $this->assetSources->getInfo($item->get('image'));
        // if ($asset) {
        //     $manager    = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        //     $image      = $manager->read($asset->getContents());
        //     $image->fit(320, 320);
        //     $encoded                             = $image->toJpeg(90);
        //     $base64                              = base64_encode((string)$encoded);
        //     $vcard['PHOTO;ENCODING=b;TYPE=JPEG'] = $base64;
        // }

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
