<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Svg extends AbstractComponent
{
    use Traits\AssetsTrait;

    protected string $tag   = 'svg';
    protected bool $oneline = true;

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            $data = ['src' => $data];
        }
        if (isset($data['value'])) {
            $data['src'] = $data['value'];
            unset($data['value']);
        }
        if (isset($data['fallback']) && strpos($data['src'],'.svg') === false) {
            $data['src'] = $data['fallback'];
            unset($data['fallback']);
        }
        return $data;
    }

    public function build(array $data, array $style, array $options) : void
    {
        

        //<svg xmlns="http://www.w3.org/2000/svg" viewBox="0.27 0.06 2083.57 597.44" class="fill-current h-10 text-darkest"><use xlink:href="#cfcd20"></use></svg>
        
        $this->setAttribute('src', $data['src']);
        $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $this->addStyle($style);
        $this->setAttribute('viewBox', '');
        $this->setContent('<use xlink:href=""></use>');
    }

    public function process(Request $request, $handler) : Response
    {
        $response = $handler->handle($request);
        $html     = (string) $response->getBody();
        $html     = str_replace("<svg></svg>\n", "<dummysvg></dummysvg>\n", $html);
        $oneline  = str_replace("\n", '', $html);
        $oneline  = preg_replace('/\s\s+/', ' ', $oneline);
        preg_match_all('/<svg(.|\n)*?src="(.*?)" xmlns(.|\n)*?<\/svg>/', $oneline, $matches);

        if (0 === count($matches[0] ?? [])) {
            $html = str_replace("    <dummysvg></dummysvg>\n", '', $html);
        } else {
            $defs = '';
            $id   = 0;
            $svgs = [];
            foreach ($matches[2] as $i => $src) {
                if (!isset($svgs[$src])) {
                    try {
                        $file = $this->environment->getAssetSources()->getFilename($src);
                    } catch (\Exception $e) {
                        $file = __DIR__.'/missing.svg';
                    }
                    if (null !== $file) {
                        $svgData    = new SvgData($file);
                        $svgs[$src] = [
                            'id'      => mb_substr(md5((string) $id), 0, 6),
                            'viewbox' => $svgData->getViewbox(),
                            'def'     => $svgData->getDef(),
                        ];
                        ++$id;
                        $def  = '<g id="'.$svgs[$src]['id'].'">';
                        $def .= $svgs[$src]['def'];
                        $def .= "</g>\n";
                        $def = preg_replace('!\s+!', ' ', $def);
                        $def = str_replace("\n", ' ', $def);
                        $defs .= '        '.$def."\n";
                    }
                }
                $with = $matches[0][$i];
                $with = str_replace(' src="'.$src.'"', '', $with);
                $with = str_replace('xlink:href=""', 'xlink:href="#'.$svgs[$src]['id'].'"', $with);
                $with = str_replace('viewBox=""', 'viewBox="'.$svgs[$src]['viewbox'].'"', $with);
                $html = str_replace($matches[0][$i], $with, $html);
            }
            $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: none;">';
            $svg .= "\n      <defs>\n";
            $defs = str_replace('></path>', '/>', $defs);
            $svg .= $defs;
            $svg .= "      </defs>\n    </svg>";
            $svg = preg_replace('/<title>.*?<\/title>/', '', $svg);
            $html = str_replace("<dummysvg></dummysvg>\n", $svg."\n", $html);
        }
        $streamFactory = new StreamFactory();
        $stream        = $streamFactory->createStream($html);
        return $response->withBody($stream);
    }
}
