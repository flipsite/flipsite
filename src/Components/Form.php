<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Form extends AbstractGroup
{
    use Traits\ReaderTrait;
    use Traits\RequestTrait;
    use Traits\PathTrait;

    protected string $tag  = 'form';

    // public function normalize(string|int|bool|array $data) : array
    public function build(array $data, array $style, string $appearance) : void
    {
        $query = $this->request->getQueryParams();
        $page  = $this->path->getPage();

        // if (isset($query['res']) && 'success' === $query['res']) {
        //     $componentData                   = ['success' => $data->get('success', true)];
        //     $componentData['success']['url'] = $page.'#'.$this->attributes['id'];
        //     $components                      = $this->builder->build($componentData, $data->getStyle(), $data->getAppearance());
        //     $this->addChildren($components);
        //     return;
        // }
        // if (isset($query['res']) && 'error' === $query['res']) {
        //     $componentData                 = ['error' => $data->get('error', true)];
        //     $componentData['error']['url'] = $page.'#'.$this->attributes['id'];
        //     $components                    = $this->builder->build($componentData, $data->getStyle(), $data->getAppearance());
        //     $this->addChildren($components);
        //     return;
        // }
        unset($data['success'], $data['error']);
        $formData = $this->reader->get('forms.'.$data['id']);
        $this->setAttribute('id', $data['id']);

        $this->setAttribute('action', 'form/submit/'.$data['id']);
        $this->setAttribute('method', $formData->method ?? 'post');
        if (isset($formData['data'])) {
            $this->setAttribute('data-validate', str_replace('"', "'", json_encode(['data'=>$formData['data'], 'required'=>$formData['required'] ?? []])));
        }
        unset($data['id']);
        $this->builder->dispatch(new Event('global-script', 'form', file_get_contents(__DIR__.'/form.js')));

        parent::build($data, $style, $appearance);
    }
}
