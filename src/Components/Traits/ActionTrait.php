<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait ActionTrait
{
    use PathTrait;
    use EnvironmentTrait;
    use SiteDataTrait;

    private function getActionAttributes(array $data) : array
    {
        if ('auto' === $data['_action']) {
            $auto            = $this->handleAuto($data['_target'] ?? '');
            $data['_action'] = $auto['_action'];
            $data['_target'] = $auto['_target'] ?? null;
        }
        switch ($data['_action']) {
            case 'tel':
                return [
                    'tag'  => 'a',
                    'href' => isset($data['_target']) ? 'tel:+'.str_replace('+', '', $data['_target']) : '#'
                ];
            case 'mailto':
                return [
                    'tag'  => 'a',
                    'href' => isset($data['_target']) ? 'mailto:'.str_replace('+', '', $data['_target']) : '#'
                ];
            case 'page':
                if ('home' === ($data['_target'] ?? '')) {
                    $data['_target'] = '';
                }
                if (isset($data['_target'])) {
                    $path = $this->siteData->getSlugs()->getPath($data['_target'], $this->path->getLanguage(), $this->path->getPage());
                    return [
                        'tag'  => 'a',
                        'href' => $this->environment->getUrl($path ?? '')
                    ];
                } else {
                    return [
                        'tag'  => 'a',
                        'href' => '#'
                    ];
                }
                // no break
            case 'url':
                return [
                    'tag'  => 'a',
                    'href' => $data['_target'] ?? '#',
                    'rel'  => 'noopener noreferrer',
                ];
            case 'url-blank':
                return [
                    'tag'    => 'a',
                    'href'   => $data['_target'] ?? '#',
                    'rel'    => 'noopener noreferrer',
                    'target' => '_blank'
                ];
            case 'download':
                if (!isset($data['_target'])) {
                    return [
                        'tag'      => 'a',
                        'href'     => '#',
                        'download' => true
                    ];
                }
                $file = $this->environment->getAssetSources()->addBasePath(\Flipsite\Assets\Sources\AssetType::FILE, $data['_target']);
                return [
                    'tag'      => 'a',
                    'href'     => $file ?? '#',
                    'download' => true
                ];
            case 'scroll':
                return [
                    'tag'  => 'a',
                    'href' => isset($data['_target']) ? '#'.trim($data['_target'], '#') : '#',
                ];
            case 'scrollLeft':
            case 'scrollRight':
                return [
                    'tag'     => 'button',
                    'onclick' => "javascript:scrollX('".trim($data['_target'])."','".($data['_action'] === 'scrollLeft' ? 'left' : 'right')."')",
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
        return [];
    }

    private function handleAuto(string $target) : array
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
