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
            $auto            = $this->handleAuto($data['_target'] ?? '');
            $data['_action'] = $auto['_action'];
            $data['_target'] = $auto['_target'] ?? null;
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
                if (!isset($data['_target'])) {
                    return [
                        'tag'      => 'a',
                        'href'     => '#',
                        'download' => true
                    ];
                }
                $file       = $this->environment->getAssetSources()->addBasePath(\Flipsite\Assets\Sources\AssetType::FILE, $data['_target']);
                $attributes = [
                    'tag'      => 'a',
                    'href'     => $file ?? '#',
                    'download' => true
                ];
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
        if ('toggle' === ($data['_onclick'] ?? '')) {
            $attributes['onclick'] = 'javascript:toggle(this)';
        }
        return $attributes;
    }

    private function handleAuto(string $target): array
    {
        $action = 'none';
        $target = str_replace('mailto:', '', $target);
        $target = str_replace('tel:', '', $target);

        $page = $this->siteData->getSlugs()->getPage($target);
        $file = $this->environment->getAssetSources()->getInfo($target);

        if ($file) {
            $action = 'download';
        } elseif ($page) {
            $action = 'page';
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
            '_action' => $action,
            '_target' => $target,
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
                $new .= ' '.$attr.'="'.$val.'"';
            }
            $html = str_replace('href="'.$href.'"', $new, $html);
        }
        return $html;
    }
}
