<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait PhoneFilterTrait
{
    protected function parsePhone(string $html, string $format = 'international'): string
    {
        if ('international' === $format) {
            return $html;
        }

        // Load HTML into DOM
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true); // Suppress HTML5 warnings
        $dom->loadHTML(mb_convert_encoding('<html><body><p>'.$html.'</p></body></html>', 'HTML-ENTITIES', 'UTF-8'));

        // Walk through all text nodes
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//text()') as $textNode) {
            /** @var \DOMText $textNode */
            $originalText = $textNode->nodeValue;

            // Replace raw phone numbers in text only
            $formattedText = preg_replace_callback(
                '/\+(\d{7,15})/',
                function ($matches) use ($format) {
                    $number = '+' . $matches[1];
                    return $this->convertToPhoneFormat($number, $format);
                },
                $originalText
            );

            if ($formattedText !== $originalText) {
                $textNode->nodeValue = $formattedText;
            }
        }

        $xpath = new \DOMXPath($dom);
        $p     = $xpath->query('//body//p')->item(0);

        if ($p) {
            $innerHtml = '';
            foreach ($p->childNodes as $child) {
                $innerHtml .= $dom->saveHTML($child);
            }
            return $innerHtml;
        }

        return $innerHTML;
    }

    protected function convertToPhoneFormat(string $number, string $format): string
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $phoneNumber = $phoneUtil->parse($number, '');
        } catch (\libphonenumber\NumberParseException $e) {
            return $number;
        }
        switch ($format) {
            case 'e164':
                $format = \libphonenumber\PhoneNumberFormat::E164;
                break;
            case 'rfc3966':
                $format = \libphonenumber\PhoneNumberFormat::RFC3966;
                break;
            case 'national':
                $format = \libphonenumber\PhoneNumberFormat::NATIONAL;
                break;
            case 'international':
            default:
                $format = \libphonenumber\PhoneNumberFormat::INTERNATIONAL;
        }
        return $phoneUtil->format($phoneNumber, $format);
    }
}
