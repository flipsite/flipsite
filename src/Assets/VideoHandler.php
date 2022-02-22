<?php

declare(strict_types=1);
namespace Flipsite\Assets;

use Psr\Http\Message\ResponseInterface as Response;

final class VideoHandler
{
    private array $extensions = ['ogg', 'webm', 'mp4'];

    public function __construct(private string $assetDir, private string $cacheDir, private string $videoBasePath = '/videos')
    {
    }

    public function getSources(string $video) : array
    {
        $sources                       = [];
        $pathinfo                      = pathinfo($video);
        $files[$pathinfo['extension']] = $pathinfo['filename'];
        $dir                           = $this->assetDir.'/'.$pathinfo['dirname'];
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if (str_starts_with($file, $pathinfo['filename'])) {
                    $tmp = pathinfo($file);
                    $ext = $tmp['extension'];
                    if ($ext !== $pathinfo['extension'] && in_array($ext, $this->extensions)) {
                        $files[$ext] = $tmp['filename'];
                    }
                }
            }
        }
        $vidDir = $this->videoBasePath;
        if ('.' !== $pathinfo['dirname']) {
            $vidDir .= '/'.$pathinfo['dirname'];
        }

        foreach ($files as $ext => $name) {
            $filepath               = $dir.'/'.$name.'.'.$ext;
            $hash                   = substr(md5_file($filepath), 0, 6);
            $src                    =
            $sources['video/'.$ext] = $vidDir.'/'.$name.'.'.$hash.'.'.$ext;
        }
        return $sources;
    }

    public function getResponse(Response $response, string $path) : Response
    {
        if ($this->inCache($path)) {
            return $this->getCached($response, $path);
        }
        $parts = preg_split('/\.[0-9a-f]{6}\./', $path);
        if (1 === count($parts)) {
            $parts = explode('.', $parts[0]);
        }
        $filename = implode('.', $parts);
        if (file_exists($this->assetDir.'/'.$filename)) {
            $filesystem = new \Symfony\Component\Filesystem\Filesystem($this->cacheDir, 0777);
            $filesystem->copy($this->assetDir.'/'.$filename, $this->cacheDir.'/'.$path);
        }
        return $this->getCached($response, $path);
    }

    private function inCache(string $path) : bool
    {
        return file_exists($this->cacheDir . '/' . $path);
    }

    private function getCached(Response $response, string $path) : Response
    {
        $filename = $this->cacheDir . '/' . $path;
        $pathinfo = pathinfo($filename);
        $body     = $response->getBody();
        $body->rewind();
        $body->write(file_get_contents($filename));
        return $response->withHeader('Content-type', 'video/'.$pathinfo['extension']);
    }
}
