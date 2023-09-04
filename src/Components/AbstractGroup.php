<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractGroup extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\StyleOptimizerTrait;
    use Traits\UrlTrait;
    use Traits\SlugsTrait;
    use Traits\ReaderTrait;
    use Traits\PathTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, array $options): void
    {
        foreach ($data['_attr'] ?? [] as $attr => $val) {
            $this->setAttribute($attr, $val);
        }
        unset($data['_attr']);
        $this->tag ??= $style['tag'];
        unset($style['tag']);

        $this->addStyle($style);

        $repeatTpl   = $data['_repeatTpl'] ?? false;
        $repeatData  = $data['_repeatData'] ?? false;
        if ($repeatTpl) {
            unset($data['_repeatTpl'], $data['_repeatData']);
            $children = [];
            $total    = count($repeatData);

            foreach ($repeatData as $i => $repeatDataItem) {
                foreach ($repeatTpl as $type => $repeatTplComponent) {
                    if (!is_array($repeatTplComponent)) {
                        $repeatTplComponent = ['value'=>$repeatTplComponent];
                    }
                    $repeatTplComponent['_dataSource'] = $repeatDataItem;
                    $optimizedStyle                    = $this->optimizeStyle($style[$type] ?? [], $i, $total);
                    if (isset($optimizedStyle['background'])) {
                        $optimizedStyle['background'] = $this->optimizeStyle($optimizedStyle['background'], $i, $total);
                    }
                    $children[] = $this->builder->build($type, $repeatTplComponent, $optimizedStyle, $options);
                }
            }
            $this->addChildren($children);
        } else {
            $children = [];
            $i        = 0;
            $total    = count($data);
            foreach ($data as $type => $componentData) {
                $componentStyle = $this->optimizeStyle($style[$type] ?? [], $i, $total);
                $children[]     = $this->builder->build($type, $componentData ?? [], $componentStyle, $options);
                $i++;
            }

            $this->addChildren($children);
        }
    }

    public function normalize(string|int|bool|array $data): array
    {
        $data = $this->normalizeAction($data);
        $data = $this->normalizeRepeat($data);
        return $data;
    }

    private function normalizeAction(string|int|bool|array $data): array
    {
        if (isset($data['_page'])) {
            $data['_target'] = $data['_page'];
            unset($data['_page']);
        }
        $action = $data['_action'] ?? false;
        $target = $data['_target'] ?? false;
        $params = $data['_params'] ?? false;

        unset($data['_action'],$data['_target'],$data['_params']);

        if (!$action) {
            return $data;
        }
        $replace   = [];
        $this->tag = 'a';

        if ('auto' === $action) {
            if (!$target) {
                return $data;
            }
            $target = str_replace('mailto:', '', $target);
            $target = str_replace('tel:', '', $target);
            $page   = $this->slugs->getPage($target);
            if ($page) {
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
        }
        switch ($action) {
            case 'page':
            case 'url':
            case 'url-blank':
                if (!$target) {
                    return $data;
                }
                if ($params) {
                    $target = $this->addTargetParams($target, $params);
                }

                $external              = false;
                $data['_attr']['href'] = $this->url($target, $external);
                if ($external) {
                    $data['_attr']['rel'] = 'noopener noreferrer';
                    if ('url-blank' === $action) {
                        $data['_attr']['target'] = '_blank';
                    }
                }
                break;
            case 'tel':
                $phoneUtil  = \libphonenumber\PhoneNumberUtil::getInstance();
                try {
                    $tel         = '+'.trim($target, '+');
                    $numberProto = $phoneUtil->parse($tel, '');
                    $replace     = [
                        '{{tel}}'      => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL),
                        '{{tel.int}}'  => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL),
                        '{{tel.e164}}' => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164),
                    ];
                } catch (\libphonenumber\NumberParseException $e) {
                }
                $data['_attr']['href'] = 'tel:'.$tel;
                break;
            case 'mailto':
                $data['_attr']['href'] = 'mailto:'.$target;
                $replace               = [
                    '{{email}}' => $target
                ];
                break;
            case 'scroll':
                $data['_attr']['href'] = '#'.trim($target, '#');
                break;
            case 'submit':
                $this->tag             = 'button';
                $data['_attr']['type'] = 'submit';
                break;
            case 'toggle':
                $this->tag             = 'button';
                $this->setAttribute('onclick', 'javascript:toggle(this)');
                $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__.'/../../js/toggle.min.js')));
                break;
            default:
                $data['_attr']['href'] = '#';
        }
        return $data;
    }

    private function normalizeRepeat(string|int|bool|array $data): array
    {
        if (isset($data['_dataSourceList'])) {
            $dataSourceList = $data['_dataSourceList'];
            unset($data['_dataSourceList']);
            if ('_none' === $dataSourceList) {
                return $data;
            }
        } else {
            return $data;
        }
        if (is_string($dataSourceList) && str_starts_with($dataSourceList, '_pages')) {
            $dataSourceList = $this->getPages(intval(str_replace('_pages-', '', $dataSourceList)));
        } elseif (is_string($dataSourceList) && '_social' === $dataSourceList) {
            $dataSourceList = $this->getSocial();
        }

        if (isset($data['_options']['filter'], $data['_options']['filterBy'])) {
            $filter         = $data['_options']['filter'];
            $filterBy       = $data['_options']['filterBy'];
            $dataSourceList = array_values(array_filter($dataSourceList, function ($item) use ($filter, $filterBy) {
                return $item[$filterBy] === $filter;
            }));
        }

        if (isset($data['_options']['offset']) || isset($data['_options']['length'])) {
            $offset         = intval($data['_options']['offset'] ?? 0);
            $length         = intval($data['_options']['length'] ?? 999999);
            $dataSourceList = array_splice($dataSourceList, $offset, $length);
        }

        if (isset($data['_options']['sortBy'])) {
            $sortField = $data['_options']['sortBy'];
            uasort($dataSourceList, function ($a, $b) use ($sortField) {
                if (isset($a[$sortField],$b[$sortField])) {
                    return $a[$sortField] <=> $b[$sortField];
                }
                return 0;
            });
        }
        if (isset($data['_options']['sort']) && 'desc' === $data['_options']['sort']) {
            $dataSourceList = array_reverse($dataSourceList);
        }

        $components = array_filter($data, function ($key): bool {
            return !str_starts_with($key, '_');
        }, ARRAY_FILTER_USE_KEY);
        foreach (array_keys($components) as $key) {
            unset($data[$key]);
        }
        $data['_repeatTpl']  = $components;
        $data['_repeatData'] = $dataSourceList ?? [];
        return $data;
    }

    private function getPages(int $level): array
    {
        $pages      = [];
        $all        = $this->slugs->getPages();
        $firstExact = false;
        if ($level === 0) {
            $pages = array_filter($all, function ($value) {
                return mb_strpos((string)$value, '/') === false;
            });
        } else {
            $parts           = explode('/', $this->path->getPage());
            $startsWith      = implode('/', array_splice($parts, 0, $level));
            foreach ($all as $page) {
                $count = substr_count((string)$page, '/');
                if (str_starts_with((string)$page, $startsWith) && $count >= $level - 1 && $count <= $level) {
                    $pages[] = $page;
                }
            }
        }

        $items = [];
        foreach ($pages as $page) {
            $item            = [
                'slug'  => $page,
                'name'  => $this->reader->getPageName((string)$page, $this->path->getLanguage())
            ];
            $items[] = $item;
        }
        return $items;
    }

    private function getSocial(): array
    {
        $name     = $this->reader->get('name');
        $language = $this->path->getLanguage();
        $items    = [];
        $i        = 0;
        foreach ($this->reader->get('social') as $type => $handle) {
            $item        = \Flipsite\Utils\SocialHelper::getData($type, (string)$handle, $name, $language);
            $item['url'] = $item['url'];
            $items[]     = $item;
        }
        return $items;
    }

    private function addTargetParams(string $target, string $params) : string
    {
        $params = explode(',', $params);
        $parts  = explode('/', $target);
        foreach ($parts as &$part) {
            if (str_starts_with($part, ':') && str_ends_with($part, ']')) {
                $part = array_shift($params);
            }
        }
        return implode('/', $parts);
    }
}
