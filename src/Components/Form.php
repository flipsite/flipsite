<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Form extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\ReaderTrait;
    protected string $tag  = 'form';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));

        $options  = $data->get('options', true);
        $formData = $this->reader->get('forms.'.$this->attributes['id']);

        $this->setAttribute('action', 'form/submit/'.$this->attributes['id']);
        $this->setAttribute('method', $formData->method ?? 'post');
        $this->setAttribute('data-validate', str_replace('"', "'", json_encode(['data'=>$formData['data'], 'required'=>$formData['required'] ?? []])));

        foreach ($data->get() as $name => $input) {
            $row = new Element('div');
            $row->addStyle($data->getStyle('row'));

            $label = new Element('label', true);
            $label->setAttribute('for', $name);
            $label->setContent($input['label']);
            $label->addStyle($data->getStyle('label'));
            $row->addChild($label);
            $row->addChild($this->getInput($input, $name, $data));
            $this->addChild($row);
        }
        $this->builder->dispatch(new Event('global-script', 'form', file_get_contents(__DIR__.'/form.js')));
    }

    private function getInput(array $inputData, string $name, ComponentData $data) : ?AbstractElement
    {
        switch ($inputData['type']) {
            case 'text':
            case 'email':
            case 'tel':
            case 'url':
            case 'date':
                $input = new Element('input', true, true);
                $input->addStyle($data->getStyle('input'));
                $input->setAttribute('name', $name);
                $input->setAttribute('type', $inputData['type']);
                if ($inputData['placeholder'] ?? $inputData['label']) {
                    $input->setAttribute('placeholder', $inputData['placeholder'] ?? $inputData['label']);
                }
                return $input;
            case 'textarea':
                $textarea = new Element('textarea', true, false);
                $textarea->addStyle($data->getStyle('textarea'));
                $textarea->setAttribute('name', $name);
                $textarea->setAttribute('type', $inputData['type']);
                if ($inputData['placeholder'] ?? $inputData['label']) {
                    $textarea->setAttribute('placeholder', $inputData['placeholder'] ?? $inputData['label']);
                }
                return $textarea;
        }
        return null;
    }
}
