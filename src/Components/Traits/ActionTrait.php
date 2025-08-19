<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait ActionTrait
{
    use PathTrait;
    use EnvironmentTrait;
    use SiteDataTrait;

    private function getActionAttributes(array $data): array
    {
        if ('auto' === $data['_action']) {
            $auto              = $this->handleAuto($data['_target'] ?? '');
            $data['_action']   = $auto['_action'];
            $data['_target']   = $auto['_target'] ?? null;
            $data['_fragment'] = $auto['_fragment'] ?? null;
        }

        $attributes = [];
        switch ($data['_action']) {
            case 'tel':
                $attributes = [
                    'tag'  => 'a',
                    'href' => isset($data['_target']) ? 'tel:+'.str_replace('+', '', $data['_target']) : '#'
                ];
                break;
            case 'mailto':
                $attributes = [
                    'tag'  => 'a',
                    'href' => isset($data['_target']) ? 'mailto:'.str_replace('+', '', $data['_target']) : '#'
                ];
                break;
            case 'page':

                if (!isset($data['_target'])) {
                    return ['tag' => 'a', 'href' => '#'];
                }
                $tmp             = explode('#', $data['_target']);
                $data['_target'] = $tmp[0];
                if (isset($tmp[1])) {
                    $data['_fragment'] = $tmp[1];
                }
                if ('home' === ($data['_target'] ?? '')) {
                    $data['_target'] = '';
                }
                if (isset($data['_target'])) {
                    $path       = $this->siteData->getSlugs()->getPath($data['_target'], $this->path->getLanguage(), $this->path->getPage());
                    $attributes = [
                        'tag'  => 'a',
                        'href' => $this->environment->getUrl($path ?? '')
                    ];
                } else {
                    $attributes = [
                        'tag'  => 'a',
                        'href' => '#'
                    ];
                }
                break;
            case 'url':
                $attributes = [
                    'tag'  => 'a',
                    'href' => $data['_target'] ?? '#',
                    'rel'  => 'noopener noreferrer',
                ];
                break;
            case 'url-blank':
                $attributes = [
                    'tag'    => 'a',
                    'href'   => $data['_target'] ?? '#',
                    'rel'    => 'noopener noreferrer',
                    'target' => '_blank'
                ];
                break;
            case 'download':
            case 'file':
                if (!isset($data['_target'])) {
                    return [
                        'tag'      => 'a',
                        'href'     => '#'
                    ];
                }
                $file       = $this->environment->getAssetSources()->addBasePath(\Flipsite\Assets\Sources\AssetType::FILE, $data['_target']);
                $attributes = [
                    'tag'      => 'a',
                    'href'     => $file ?? '#',
                    'download' => $data['_filename'] ?? true
                ];
                if ('file' === $data['_action']) {
                    $attributes['rel'] = 'noopener noreferrer';
                    unset($attributes['download']);
                }
                break;
            case 'scroll':
                $attributes = [
                    'tag'  => 'a',
                    'href' => isset($data['_target']) ? '#'.trim($data['_target'], '#') : '#',
                ];
                break;
            case 'scrollX':
            case 'scrollLeft':
            case 'scrollRight':
                $direction = $data['_target'] ?? 'left';
                if ('scrollLeft' === $data['_action']) {
                    $direction = 'left';
                } elseif ('scrollRight' === $data['_action']) {
                    $direction = 'right';
                }
                return [
                    'tag'     => 'button',
                    'onclick' => "javascript:scrollX(this,'".$direction."')",
                ];
            case 'submit':
                return [
                    'tag'  => 'button',
                    'type' => 'submit',
                ];
            case 'toggle':
                return [
                    'tag'     => 'button',
                    'onclick' => 'javascript:toggle(this)',
                ];
        }
        if (isset($attributes['href']) && ($data['_blank'] ?? false)) {
            $attributes['target'] = '_blank';
        }
        if (isset($attributes['href']) && isset($data['_fragment']) && substr($data['_fragment'], 0, 1) !== '{' && substr($data['_fragment'], -1) !== '}' && !!trim($data['_fragment'])) {
            if (str_contains($attributes['href'], '#')) {
                $attributes['href'] .= '#'.$data['_fragment'];
            } else {
                $attributes['href'] .= '#'.$data['_fragment'];
            }
        }
        if (isset($data['_onclick'])) {
            try {
                $onclick = json_decode($data['_onclick'], true);
            } catch (\JsonException $e) {
                $onclick = [];
            }
            $attributes['onclick'] = [];

            if (in_array('toggle', $onclick)) {
                $attributes['onclick'][] = 'toggle(this)';
            }
            if (in_array('remember', $onclick)) {
                $this->builder->dispatch(new \Flipsite\Builders\Event('ready-script', 'remember', file_get_contents(__DIR__ . '/../../../js/dist/remember.min.js')));
                $attributes['onclick'][] = 'remember(this)';
            }
            if (count($attributes['onclick'])) {
                $attributes['onclick'] = 'javascript:'.implode(';', $attributes['onclick']);
            }
        }
        return $attributes;
    }

    private function handleAuto(string $target): array
    {
        $action = null;
        $target = str_replace('mailto:', '', $target);
        $target = str_replace('tel:', '', $target);

        $parts    = explode('#', $target);
        $target   = $parts[0];
        $openFile = false;
        if (str_starts_with($target, 'file://')) {
            $openFile = true;
            $target   = str_replace('file://', '', $target);
        }
        $fragment = $parts[1] ?? null;

        $page = $this->siteData->getSlugs()->getPage($target);
        $file = $this->environment->getAssetSources()->getInfo($target);

        if ($file) {
            $action = 'download';
            if ($openFile) {
                $action = 'file';
            }
        } elseif ($page) {
            $action = 'page';
            if ($target === '') {
                $action = null;
            }
        } elseif (str_starts_with($target, 'http')) {
            $action = 'url-blank';
        } elseif (filter_var($target, FILTER_VALIDATE_EMAIL)) {
            $action = 'mailto';
        } else {
            $matches = [];
            preg_match('/\+[0-9]{9,20}/', $target, $matches);
            if (count($matches)) {
                $action = 'tel';
            }
        }
        return [
            '_action'   => $action,
            '_target'   => $target,
            '_fragment' => $fragment
        ];
    }

    private function fixUrlsInHtml(string $html): string
    {
        $html    = str_replace(' rel="noopener noreferrer" target="_blank"', '', $html);
        $matches = [];
        preg_match_all('/[ ]{1}href="(.*?)"/', $html, $matches);
        if (0 === count($matches[1])) {
            return $html;
        }
        $hrefs = array_unique($matches[1]);

        foreach ($hrefs as $href) {
            $actionAttributes = $this->getActionAttributes([
                '_action' => 'auto',
                '_target' => trim($href, '/')
            ]);
            unset($actionAttributes['tag']);
            $new = 'href="'.($actionAttributes['href'] ?? '#missing').'"';
            unset($actionAttributes['href']);
            foreach ($actionAttributes as $attr => $val) {
                if (is_bool($val)) {
                    $new .= ' '.$attr;
                } else {
                    $new .= ' '.$attr.'="'.$val.'"';
                }
            }
            $html = str_replace('href="'.$href.'"', $new, $html);
        }
        return $html;
    }
}
