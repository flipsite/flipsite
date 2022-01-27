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

        if (isset($query['res']) && 'success' === $query['res']) {
            parent::build($data['success'] ?? [], $style['success'] ?? [], $appearance);
            return;
        }
        if (isset($query['res']) && 'error' === $query['res']) {
            parent::build($data['error'] ?? [], $style['error'] ?? [], $appearance);
            return;
        }
        unset($data['success'], $data['error']);
        $formData = $this->reader->get('forms.'.$data['id']);
        $this->setAttribute('id', $data['id']);

        $this->setAttribute('action', $data['action'] ?? 'form/submit/'.$data['id']);
        $this->setAttribute('method', $formData->method ?? 'post');
        if (isset($formData['data'])) {
            $this->setAttribute('data-validate', str_replace('"', "'", json_encode(['data'=>$formData['data'], 'required'=>$formData['required'] ?? []])));
        }
        unset($data['id']);
        $this->builder->dispatch(new Event('global-script', 'form', file_get_contents(__DIR__.'/../../js/form.min.js')));

        parent::build($data, $style, $appearance);
    }
}
