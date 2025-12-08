<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Counter extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected string $tag  = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        if (isset($data['value'])) {
            $parts        = explode('|', $data['value']);
            if (count($parts) === 1) {
                $data['to']   = floatval($parts[0] ?? 100);
            } else {
                $data['from'] = floatval($parts[0] ?? 0);
                $data['to']   = floatval($parts[1] ?? 100);
            }
            unset($data['value']);
        }
        $this->builder->dispatch(new \Flipsite\Builders\Event('ready-script', 'counter', file_get_contents(__DIR__.'/../../js/dist/counter.min.js')));
        $this->setAttribute('data-counter', true);
        $this->setAttribute('data-timing', (string)($data['timing'] ?? 'ease-in-out'));
        $this->setAttribute('data-to', (string)($data['from'] ?? 0));
        $this->setAttribute('data-to', (string)($data['to'] ?? 100));
        $this->setAttribute('data-duration', (string)($data['duration'] ?? 500));
        if (isset($data['thousandsSeparator'])) {
            $this->setAttribute('data-thousands', (string)($data['thousandsSeparator']));
        }
        if (isset($data['decimals'])) {
            $this->setAttribute('data-decimals', (string)($data['decimals']));
        }
        if (isset($data['decimalSeparator'])) {
            $this->setAttribute('data-decimal-separator', (string)($data['decimalSeparator']));
        }

        // Format the initial display value
        $initialValue = (string)($data['from'] ?? 0);
        if (isset($data['decimals']) || isset($data['decimalSeparator']) || isset($data['thousandsSeparator'])) {
            $separators = [
                'none'       => '',
                'space'      => ' ',
                'comma'      => ',',
                'period'     => '.',
                'apostrophe' => '\'',
            ];
            $decimals           = intval($data['decimals'] ?? 0);
            $decimalSeparator   = $data['decimalSeparator'] ?? 'period';
            $thousandsSeparator = $data['thousandsSeparator'] ?? 'space';
            $initialValue       = number_format(floatval($initialValue), $decimals, $separators[$decimalSeparator], $separators[$thousandsSeparator]);
        }

        $value = new Element('span', true);
        $value->setContent($initialValue);
        $span = trim($value->render());
        if (isset($data['prefix']) || isset($data['suffix'])) {
            $prefix = $data['prefix'] ?? '';
            $suffix = $data['suffix'] ?? '';
            $this->setContent($prefix.$span.$suffix);
        } else {
            $this->setContent($span);
        }
    }
}
