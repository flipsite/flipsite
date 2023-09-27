<?php

declare(strict_types=1);
namespace Flipsite\Compiler;

use Flipsite\AbstractEnvironment;
use Flipsite\Data\Reader;
use Symfony\Component\Filesystem\Filesystem;

class Compiler implements Psr\Log\LoggerAwareInterface
{
    use Psr\Log\LoggerAwareTrait;
    private string $targetDir;

    public function __construct(private AbstractEnvironment $environment, string $targetDir)
    {
        // Create target dir if it does not already exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $this->targetDir = realpath($targetDir);

        putenv('SITE_DIR='.$this->environment->getSiteDir());
        putenv('VENDOR_DIR='.$this->environment->getVendorDir());
        putenv('IMG_DIR='.$this->environment->getImgDir());
        putenv('VIDEO_DIR='.$this->environment->getVideoDir());
        putenv('TRAILING_SLASH=1');
    }

    public function compile(?string $domain = null)
    {
        // Set remaining environment that needs reader
        $reader   = new Reader($this->environment);
        $config   = $reader->get('compile');
        if (!isset($config['domain']) && $domain) {
            $config['domain'] = $domain;
        }
        $basePath = $config['basePath'] ?? '';
        putenv('APP_BASEPATH='.$basePath);
        putenv('APP_ENV='.(($config['live'] ?? false) ? 'live' : 'dev'));

        $https = $config['https'] ?? false;

        // Remove all index.html files
        $this->deleteContent($this->targetDir);

        // Create pages and parse assets after each created page
        $slugs    = $reader->getSlugs();
        $allPages = array_keys($slugs->getAll());

        $assets   = [];
        foreach ($allPages as $page) {
            $requestUri = $basePath.'/'.$page;
            $html       = $this->getResponse($https, $config['domain'], $requestUri);
            $this->writeFile($this->targetDir, $page.'/index.html', $html);
            $assets = array_merge($assets, AssetParser::parse($html, $config['domain']));
        }

        // Get list of unique assets
        $assets     = array_values(array_filter(array_unique($assets)));

        // Remove base path
        foreach ($assets as &$asset) {
            $asset = str_replace($basePath,'',$asset);
        }

        $notDeleted = $this->deleteAssets($this->targetDir, $assets);
        $assets     = array_diff($assets, $notDeleted);

        // Create assets
        foreach ($assets as $asset) {;
            $source = $this->getResponse($config['https'] ?? true, $config['domain'], $asset);
            $this->writeFile($this->targetDir, str_replace($basePath,'',$asset), $source);
        }

        // Sitemap
        $requestUri = $basePath.'/sitemap.xml';
        $sitemap    = $this->getResponse($https, $config['domain'], $requestUri);
        $this->writeFile($this->targetDir, '/sitemap.xml', $sitemap);

        // Robots
        $requestUri = $basePath.'/robots.txt';
        $robots     = $this->getResponse($https, $config['domain'], $requestUri);
        $this->writeFile($this->targetDir, '/robots.txt', $robots);

        // Files
        $files = $this->getDirContents($this->environment->getSiteDir().'/files');
        $filesystem = new Filesystem();
        $filesystem->remove($this->targetDir.'/files');
        foreach ($files as $file) {
            $tmp = explode('files/', $file);
            $fileName = array_pop($tmp);
            $this->writeFile($this->targetDir, 'files/'.$fileName, file_get_contents($file));
        }
    }

    public function getFiles() : array
    {
        $files = $this->getDirContents($this->targetDir);
        return array_filter($files, function ($file) {
            return !is_dir($file);
        });
    }

    private function getResponse(bool $https, string $domain, string $requestUri): string
    {
        ob_start();
        $_SERVER['REQUEST_URI']    = $requestUri;

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME']    = '/index.php';
        if ($https) {
            $_SERVER['SERVER_PORT'] = 443;
            $_SERVER['HTTPS']       = 'on';
        } else {
            $_SERVER['SERVER_PORT'] = 80;
            unset($_SERVER['HTTPS']);
        }
        $_SERVER['HTTP_HOST']       = $domain;
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.80 Safari/537.36';
        include $this->environment->getVendorDir().'/flipsite/flipsite/src/index.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    private function writeFile(string $targetDir, string $page, string $html): string
    {
        $filename = $targetDir.'/'.$page;
        $filename = str_replace('//', '/', $filename);
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
            if ($this->logger) {
                $this->logger->info('Created directory '.dirname($filename));
            }
        }
        if (!is_dir($filename)) {
            file_put_contents($filename, $html);
            if ($this->logger) {
                $this->logger->info('Created file '.$filename);
            }
        }
        return $filename;
    }

    private function deleteContent(string $targetDir): void
    {
        $filesystem = new Filesystem();
        $files      = $this->getDirContents($targetDir);
        foreach ($files as $file) {
            if (false !== mb_strpos($file, 'index.html')) {
                $filesystem->remove($file);
            }
        }
    }

    private function deleteAssets(string $targetDir, array $assets): array
    {
        $notDeleted = [];
        $filesystem = new Filesystem();
        $images     = $this->getDirContents($targetDir.'/img');
        $dirs       = [];
        foreach ($images as $image) {
            if (is_dir($image)) {
                $dirs[] = $image;
                continue;
            }
            $asset = str_replace($targetDir, '', $image);
            if (in_array($asset, $assets)) {
                $notDeleted[] = $asset;
            } else {
                $filesystem->remove($image);
            }
        }
        $videos = $this->getDirContents($targetDir.'/videos');

        foreach ($videos as $video) {
            if (is_dir($video)) {
                $dirs[] = $video;
                continue;
            }
            $asset = str_replace($targetDir, '', $video);
            if (in_array($asset, $assets)) {
                $notDeleted[] = $asset;
            } else {
                $filesystem->remove($video);
            }
        }
        foreach ($dirs as $dir) {
            if (count(scandir($dir)) == 2) {
                $filesystem->remove($dir);
            }
        }
        return array_values(array_filter($notDeleted));
    }

    private function getDirContents(string $dir, &$results = []) : array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if (str_ends_with($value, '.DS_Store')) {
                continue;
            }
            if (!is_dir($path)) {
                $results[] = $path;
            } elseif ('.' != $value && '..' != $value) {
                $this->getDirContents($path, $results);
                $results[] = $path;
            }
        }
        return $results;
    }
}
