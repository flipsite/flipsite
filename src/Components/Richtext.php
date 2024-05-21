<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Richtext extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\ActionTrait;
    protected string $tag = 'div';

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['value' => (string)$data];
        }
        return $data;
    }

    public function build(array $data, array $style, array $options): void
    {
        if (!$data['value']) {
            return;
        }

        // If old HTML string
        if (is_string($data['value'])) {
            $data['value'] = $this->getComponentsFromHtml($data['value']);
        }

        foreach ($data['value'] as $index => $component) {
            $tag         = $component['tag'];
            $componentId = null;
            switch ($tag) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                    $data['heading:'.$index] = [
                        'value'  => $component['value'],
                        '_style' => ArrayHelper::merge($style[$tag] ?? [], ['tag' => $tag])
                    ];
                    break;
                case 'ul':
                case 'ol':
                    $data['ul:'.$index] = [

                        '_repeat'   => $component['value'],
                        '_style'    => ArrayHelper::merge($style[$tag] ?? [], ['tag' => $tag]),
                        'paragraph' => [
                            'value'  => '{item}',
                            '_style' => ArrayHelper::merge(['tag' => 'li'], [
                                'a'      => $style['a'] ?? [],
                                'strong' => $style['strong'] ?? [],
                            ])
                        ]
                    ];
                    break;
                case 'img':
                    $data['image:'.$index] = [
                        'value'  => $component['value'],
                        '_style' => ArrayHelper::merge($style[$tag] ?? [], ['tag' => $tag])
                    ];
                    break;
                case 'p':
                    $data['paragraph:'.$index] = [
                        'value'  => $component['value'],
                        '_style' => ArrayHelper::merge(['tag' => $tag], [
                            'a'      => $style['a'] ?? [],
                            'strong' => $style['strong'] ?? [],
                        ])
                    ];
                    break;
                case 'table':
                    $data['table:'.$index] = [
                        'th'      => $component['th'] ?? [],
                        'td'      => $component['td'] ?? [],
                        '_style'  => ArrayHelper::merge($style['tbl'] ?? [], [
                            'th' => $style['th'] ?? [],
                            'td' => $style['td'] ?? [],
                        ])
                    ];
                    break;
                case 'youtube':
                    $data['youtube:'.$index] = [
                        'value'  => $component['value'],
                        '_style' => $style['youtube'] ?? [],
                    ];
                    break;
            }
        }
        unset($data['value']);
        parent::build($data, $style, $options);
    }

    public function getComponentsFromHtml(string $html): array
    {
        $dom               = new \DOMDocument();
        $dom->formatOutput = true;
        $html              = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $html              = '<html><body>'.$html.'</body></html>';
        @$dom->loadHtml($html);

        libxml_clear_errors();

        // Get all span elements
        $spans = $dom->getElementsByTagName('span');

        // Remove spans while preserving their content
        while ($spans->length > 0) {
            $span   = $spans->item(0);
            $parent = $span->parentNode;

            // Move all child nodes of the span to its parent
            while ($span->firstChild) {
                $parent->insertBefore($span->firstChild, $span);
            }

            // Remove the empty span tag
            $parent->removeChild($span);
        }

        // Remove <p> tags wrapping <img> tags while preserving the <img> elements
        $paragraphs = $dom->getElementsByTagName('p');
        for ($i = $paragraphs->length - 1; $i >= 0; $i--) {
            $p = $paragraphs->item($i);
            if ($p->getElementsByTagName('img')->length > 0) {
                $newNodes = [];
                $index    = 0;
                foreach ($p->childNodes as $child) {
                    if ($child instanceof \DOMElement && $child->nodeName === 'img') {
                        $newNodes[$index++] = $child->cloneNode(true);
                    } else {
                        $newNodes[$index++] ??= [];
                        $newNodes[$index][] = $child->cloneNode(true);
                    }
                }
                $parentNode = $p->parentNode;

                foreach ($newNodes as $newNode) {
                    if (is_array($newNode) && count($newNode)) {
                        $newP = $dom->createElement('p');
                        foreach ($newNode as $child) {
                            $newP->appendChild($child);
                        }
                        $parentNode->insertBefore($newP, $p);
                    } elseif ($newNode instanceof \DOMElement) {
                        $parentNode->insertBefore($newNode, $p);
                    }
                }
                $parentNode->removeChild($p);
            } else {
                $text = trim($p->textContent);
                if (!$text) {
                    $parent = $p->parentNode;
                    $parent->removeChild($p);
                }
            }
        }

        // Remove all style attributes from all elements
        $xpath          = new \DOMXPath($dom);
        $nodesWithStyle = $xpath->query('//*[@style]');
        foreach ($nodesWithStyle as $node) {
            $node->removeAttribute('style');
        }

        $body =  $dom->getElementsByTagName('body')->item(0);

        $components = [];
        foreach ($body->childNodes as $child) {
            switch ($child->nodeName) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                    if (trim($child->textContent)) {
                        $components[] = [
                            'tag'    => $child->nodeName,
                            'value'  => trim($child->textContent),
                        ];
                    }
                    break;
                case 'iframe':
                    $src = $child->getAttribute('src');
                    if (strpos($src, 'youtube')) {
                        $tmp          = explode('/', $src);
                        $components[] = [
                            'tag'    => 'youtube',
                            'value'  => array_pop($tmp)
                        ];
                    }
                    break;
                case 'img':
                    $src = $child->getAttribute('src');
                    if (strpos($src, '@')) {
                        $pathinfo = pathinfo($src);
                        $tmp      = explode('@', $pathinfo['basename']);
                        $src      = $tmp[0].'.'.$pathinfo['extension'];
                    }
                    $components[] = [
                        'tag'    => $child->nodeName,
                        'value'  => $src,
                    ];
                    break;
                case 'p':
                    $components[] = [
                        'tag'    => 'p',
                        'value'  => $this->getMarkdown($child)
                    ];
                    break;
                case 'ul':
                case 'ol':
                    $items = [];
                    foreach ($child->childNodes as $item) {
                        if ($item->nodeName === 'li') {
                            $items[] = $this->getMarkdown($item);
                        }
                    }
                    $components[] = [
                        'tag'    => $child->nodeName,
                        'value'  => json_encode($items)
                    ];
                    break;
                case 'pre':
                    $tableDom             = new \DOMDocument();
                    $html                 = str_replace('&nbsp;', '', $child->textContent);
                    $tableDom->loadHtml($html);
                    libxml_clear_errors();
                    $table = $tableDom->getElementsByTagName('table')[0];
                    if ($table) {
                        $index = 0;
                        $th    = [];
                        $td    = [];
                        foreach ($table->childNodes as $row) {
                            if ('tr' === $row->nodeName) {
                                foreach ($row->childNodes as $cell) {
                                    if ('th' === $cell->nodeName) {
                                        $th[] = trim($cell->textContent);
                                    }
                                    if ('td' === $cell->nodeName) {
                                        $td[$index] ??= [];
                                        $td[$index][] = trim($cell->textContent);
                                    }
                                }
                                $index++;
                            }
                        }
                        $components[] = [
                            'tag'    => 'table',
                            'th'     => $th,
                            'td'     => $td
                        ];
                    } else {
                        $iframe = $tableDom->getElementsByTagName('iframe')[0];
                        if ($iframe) {
                            $src = $iframe->getAttribute('src');
                            if (strpos($src, 'youtube')) {
                                $tmp          = explode('/', $src);
                                $components[] = [
                                    'tag'    => 'youtube',
                                    'value'  => array_pop($tmp)
                                ];
                            }
                        }
                    }
                    break;
                default:
                    echo $child->nodeName."\n";
            }
        }
        return $components;
    }

    private function getMarkdown(\DOMNode $node) : string
    {
        $markdown = [];
        foreach ($node->childNodes as $child) {
            switch ($child->nodeType) {
                case XML_TEXT_NODE:
                    $markdown[] = $child->textContent;
                    break;
                case XML_ELEMENT_NODE:
                    switch ($child->nodeName) {
                        case 'strong':
                            $markdown[] = '**'.$child->textContent.'**';
                            break;
                        case 'em':
                            $markdown[] = '*'.$child->textContent.'*';
                            break;
                        case 'a':
                            $markdown[] = '['.$child->textContent.']('.trim($child->getAttribute('href'), '/').')';
                            break;
                        case 'br':
                            $markdown[] = '<br>';
                            break;
                        default:
                            $markdown[] = $child->textContent;
                    }
            }
        }
        return implode(' ', $markdown);
    }
}
