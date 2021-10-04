<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class ContactDetails extends Grid
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    public function with(ComponentData $data) : void
    {
        $data = $this->normalize($data, $data->get('icon', true) ?? 'icon', $data->get('text', true) ?? 'paragraph');
        parent::with($data);
    }

    protected function normalize(ComponentData $data, string $iconType, string $textType) : ComponentData
    {
        $itemData = $data->get();
        $items    = [];
        foreach ($itemData as $attr => $val) {
            if (isset($items[$attr])) {
                continue;
            }
            switch ($attr) {
                case 'company':
                    $items[$attr] = [
                        $iconType      => 'zondicons/home',
                        $textType      => $val,
                    ];
                    break;
                case 'person':
                    $items[$attr] = [
                        $iconType      => 'zondicons/user',
                        $textType      => $val,
                    ];
                    break;
                case 'address':
                    $addressFormatter = new \Adamlc\AddressFormat\Format();
                    $addressFormatter->setLocale($itemData['country']);
                    $addressFormatter['STREET_ADDRESS'] = $itemData['address'] ?? '';
                    $addressFormatter['LOCALITY']       = $itemData['city'] ?? '';
                    $addressFormatter['POSTAL_CODE']    = $itemData['zip'] ?? '';
                    $addressFormatter['ADMIN_AREA']     = $itemData['state'] ?? '';
                    $addressFormatter['COUNTRY']        = $itemData['country'] ?? '';
                    $address                            = $addressFormatter->formatAddress(false);
                    $items[$attr]                       = [
                        $iconType => 'zondicons/location',
                        $textType => $this->md(str_replace("\n", ', ', $address), $itemData['maps']),
                    ];
                    break;
                case 'phone':
                    $phones    = is_array($itemData['phone']) && !ArrayHelper::isAssociative($itemData['phone']) ? $itemData['phone'] : [$itemData['phone']];
                    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                    foreach ($phones as $i => $phone) {
                        if (is_string($phone)) {
                            $phone = ['number' => $phone];
                        }
                        try {
                            $numberProto = $phoneUtil->parse($phone['number'], '');
                        } catch (\libphonenumber\NumberParseException $e) {
                        }
                        $items['phone'.$i] = [
                            $iconType => 'zondicons/phone',
                            $textType => $this->md($phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL), 'tel:'.$phone['number']),
                        ];
                    }
                    break;
                case 'email':
                    $items[$attr] = [
                        $iconType => 'zondicons/envelope',
                        $textType => $this->md($val, 'mailto:'.$val)
                    ];
                    break;
            }
        }
        unset($items['country'],$items['zip'],$items['maps'],$items['city']);
        $data->set(array_values($items));

        return $data;
    }

    private function md(string $text, ?string $url = null) : string
    {
        if (null === $url) {
            return $text;
        }
        return '['.$text.']('.$url.')';
    }
}
