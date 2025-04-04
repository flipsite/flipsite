<?php

declare(strict_types=1);
namespace Flipsite\Compiler;

use Flipsite\EnvironmentInterface;
use Flipsite\Flipsite;
use Flipsite\Assets\Assets;
use Flipsite\Data\SiteDataInterface;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerAwareInterface;

class Compiler implements LoggerAwareInterface
{
    use \Psr\Log\LoggerAwareTrait;
    private string $targetDir;
    private array $optimizeExtensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];

    public function __construct(private EnvironmentInterface $environment, private SiteDataInterface $siteData, string $targetDir)
    {
        // Create target dir if it does not already exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $this->targetDir = realpath($targetDir);
    }

    public function compile()
    {
        $compileOptions = $this->siteData->getCompile();
        $compileOptions['domain'] ??= 'flipsite.io';
        $basePath = $compileOptions['basePath'] ?? '/';
        if ('/' !== $basePath) {
            $basePath = '/'.trim($basePath, '/').'/';
        }

        $assetList = [];
        $allFiles  = [];

        $flipsite = new Flipsite($this->environment, $this->siteData);
        // Remove all index.html files
        $this->deleteContent($this->targetDir);

        // Download fonts
        $fonts      = $this->getDirContents($this->targetDir.'/fonts');
        $filesystem = new Filesystem();
        foreach ($fonts as $font) {
            $filesystem->remove($font);
        }
        $filesystem->remove($this->targetDir.'/fonts');
        if ($this->environment->downloadFonts()) {
            $googleFonts = new \Flipsite\Utils\GoogleFonts($this->siteData->getFonts());
            $localFonts  = $googleFonts->download($this->targetDir, 'fonts', $this->environment->getAssetsBasePath());
            $this->siteData->setFonts($localFonts);
            foreach ($localFonts as $font) {
                foreach ($font['files'] ?? [] as $file) {
                    $fontFile = substr($file['src'], 4, strlen($file['src']) - 21);
                    if (str_starts_with($fontFile, $basePath)) {
                        $fontFile = substr($fontFile, strlen($basePath));
                    }
                    if (!in_array($fontFile, $allFiles)) {
                        $allFiles[] = $fontFile;
                    }
                }
            }
        }

        // Create pages and parse assets after each created page
        $allPages = array_keys($this->siteData->getSlugs()->getAll());
        $allPages = array_map('strval', $allPages);

        foreach ($allPages as $page) {
            $meta = $this->siteData->getPageMeta($page, $this->siteData->getDefaultLanguage());
            if ($meta['unpublished'] ?? false) {
                continue;
            }
            $html = $flipsite->render($page);
            if ($this->logger) {
                $this->logger->info('Created file for page '.$page);
            }

            if ('404' == $page) {
                $filepath = '404.html';
            } else {
                $filepath = trim($page.'/index.html', '/');
            }
            $fileAssets = AssetParser::parse($html, $compileOptions['domain']);
            foreach ($fileAssets as $fileAsset) {
                $optimizedAsset = $this->getOptimizedAsset($fileAsset);
                $html           = str_replace($fileAsset, $optimizedAsset, $html);
            }

            $this->writeFile($this->targetDir, $filepath, $html);
            $allFiles[] = $filepath;
            $assetList  = array_merge($assetList, $fileAssets);
        }

        // Get list of unique assets
        $assetList = array_values(array_filter(array_unique($assetList)));

        // Remove base path
        foreach ($assetList as &$asset) {
            if (str_starts_with($asset, $basePath)) {
                $asset = substr($asset, strlen($basePath));
            }
            $allFiles[] = $this->getOptimizedAsset($asset);
        }

        $this->deleteAssets($this->targetDir);

        $assetList  = json_decode(json_encode($assetList));

        $assets = new Assets($this->environment->getAssetSources());
        foreach ($assetList as $asset) {
            $filename = false;
            if (str_starts_with($asset, 'img/')) {
                $filename = substr($asset, 4);
                $asset    = $this->getOptimizedAsset($asset);
            } elseif (str_starts_with($asset, 'videos/')) {
                $filename = substr($asset, 7);
            } elseif (str_starts_with($asset, 'files/')) {
                $filename = substr($asset, 6);
            }
            if ($filename) {
                $target   = $this->targetDir.'/'.$asset;
                $pathinfo = pathinfo($target);
                if (!file_exists($pathinfo['dirname'])) {
                    mkdir($pathinfo['dirname'], 0777, true);
                }
                file_put_contents($target, $assets->getContents($filename));
            }
        }

        // Sitemap
        $this->writeFile($this->targetDir, '/sitemap.xml', $flipsite->render('sitemap.xml'));
        $allFiles[] = 'sitemap.xml';

        // Robots
        $this->writeFile($this->targetDir, '/robots.txt', $flipsite->render('robots.txt'));
        $allFiles[] = 'robots.txt';

        // Redirects
        $redirects = $this->siteData->getRedirects();
        if ($redirects && ($compileOptions['redirects'] ?? '') === 'meta') {
            $indexTpl = file_get_contents(__DIR__.'/redirect.tpl.html');
            foreach ($redirects as $redirect) {
                $file = '/'.$redirect['from'].'/index.html';
                $url  = $this->environment->getAbsoluteUrl($redirect['to']);
                $this->writeFile($this->targetDir, $file, str_replace('{{url}}', $url, $indexTpl));
                $allFiles[] = $file;
            }
        }

        // Delete files that are not needed anymore
        $allFilesInCurrentCompileDir = $this->getDirContents($this->targetDir);
        $deleteFiles                 = [];

        foreach ($allFilesInCurrentCompileDir as $currentFile) {
            if (is_dir($currentFile)) {
                continue;
            }
            $delete = true;
            foreach ($allFiles as $newFile) {
                if (str_ends_with($currentFile, $newFile)) {
                    $delete = false;
                }
            }
            if ($delete && !in_array($currentFile, $deleteFiles)) {
                $deleteFiles[] = $currentFile;
            }
        }

        foreach ($deleteFiles as $deleteFile) {
            $this->logger->info('Deleted file '.$deleteFile);
            unlink($deleteFile);
        }

        $this->removeEmptyFolders($this->targetDir);
    }

    private function getOptimizedAsset(string $asset): string
    {
        if (strpos($asset, '/files/') !== false) {
            return $asset;
        }
        $pathinfo = pathinfo($asset);
        if (!in_array($pathinfo['extension'], $this->optimizeExtensions)) {
            return $asset;
        }
        $hash     = substr(md5($pathinfo['basename']), 0, 6);
        $parts    = explode('@', $pathinfo['filename']);
        $filename = $parts[0].'-'.$hash.'.'.$pathinfo['extension'];
        return $pathinfo['dirname'].'/'.strtolower($filename);
    }

    private function removeEmptyFolders(string $dir)
    {
        // Open the directory
        $dh = opendir($dir);

        // Check if the directory could be opened
        if (!$dh) {
            throw new \Exception("Cannot open the directory $dir");
        }

        // Loop through the directory
        while (($file = readdir($dh)) !== false) {
            // Skip special directories
            if ($file != '.' && $file != '..') {
                $path = $dir . '/' . $file;

                // If it's a directory, recursively call the function
                if (is_dir($path)) {
                    $this->removeEmptyFolders($path);
                    // Check if the directory is empty after recursive call
                    $filesAfter = array_diff(scandir($path), ['.', '..']);
                    if (empty($filesAfter)) {
                        rmdir($path);
                    }
                }
            }
        }
        // Close the directory handle
        closedir($dh);
    }

    public function getFiles(): array
    {
        $files = $this->getDirContents($this->targetDir);
        return array_filter($files, function ($file) {
            return !is_dir($file);
        });
    }

    private function writeFile(string $targetDir, string $page, string $html): string
    {
        $filename = $targetDir.'/'.$page;
        $filename = str_replace('//', '/', $filename);
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
            if ($this->logger) {
                //$this->logger->info('Created directory '.dirname($filename));
            }
        }
        if (!is_dir($filename)) {
            file_put_contents($filename, $html);
            if ($this->logger) {
                //$this->logger->info('Created file '.$filename);
            }
        }
        return $filename;
    }

    private function deleteContent(string $targetDir): void
    {
        $filesystem = new Filesystem();
        $files      = $this->getDirContents($targetDir);
        foreach ($files as $file) {
            if (false !== mb_strpos($file, '404.html')) {
                $filesystem->remove($file);
            }
            if (false !== mb_strpos($file, 'index.html')) {
                $filesystem->remove($file);
            }
            if (false !== mb_strpos($file, '.htaccess')) {
                $filesystem->remove($file);
            }
        }
    }

    private function deleteAssets(string $targetDir)
    {
        $filesystem = new Filesystem();
        $filesystem->remove($targetDir.'/img');
        $filesystem->remove($targetDir.'/videos');
        $filesystem->remove($targetDir.'/files');
    }

    private function getDirContents(string $dir, &$results = []): array
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
