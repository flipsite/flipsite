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

        $flipsite = new Flipsite($this->environment, $this->siteData);
        // Remove all index.html files
        $this->deleteContent($this->targetDir);

        // Create pages and parse assets after each created page
        $allPages = array_keys($this->siteData->getSlugs()->getAll());

        $allFiles = [];
        $assetList = [];
        foreach ($allPages as $page) {
            $meta = $this->siteData->getPageMeta($page, $this->siteData->getDefaultLanguage());
            if ($meta['unpublished'] ?? false) {
                continue;
            }
            $html = $flipsite->render($page);
            if ($this->logger) {
                $this->logger->info('Created file for page '.$page);
            }
            $this->writeFile($this->targetDir, $page.'/index.html', $html);
            $allFiles[] = $page.'/index.html';
            $assetList = array_merge($assetList, AssetParser::parse($html, $compileOptions['domain']));
        }


        // Get list of unique assets
        $assetList = array_values(array_filter(array_unique($assetList)));

        // Remove base path
        foreach ($assetList as &$asset) {
            if ($basePath && $basePath !== '/') {
                $asset      = str_replace($basePath, '', $asset);
            }
            $allFiles[] = $asset;
        }

        $notDeleted = $this->deleteAssets($this->targetDir, $assetList);
        $assetList = array_values(array_diff($assetList, $notDeleted));
        $assetList = json_decode(json_encode($assetList));

        $assets = new Assets($this->environment->getAssetSources());
        foreach ($assetList as $asset) {
            $filename = false;
            if (str_starts_with($asset, '/img/')) {
                $filename = substr($asset, 5);
            } elseif (str_starts_with($asset, '/videos/')) {
                $filename = substr($asset, 8);
            } elseif (str_starts_with($asset, '/files/')) {
                $filename = substr($asset, 7);
            }
            if ($filename) {
                $target = $this->targetDir.$asset;
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
                $url = $this->environment->getAbsoluteUrl($redirect['to']);
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
            if ($delete) {
                $deleteFiles[] = $currentFile;
            }
        }

        foreach ($deleteFiles as $deleteFile) {
            unlink($deleteFile);
        }

        $this->removeEmptyFolders($this->targetDir);
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
                    if (count(glob($path . '/*')) === 0) {
                        rmdir($path); // Remove the empty directory
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
            if (false !== mb_strpos($file, 'index.html')) {
                $filesystem->remove($file);
            }
            if (false !== mb_strpos($file, '.htaccess')) {
                $filesystem->remove($file);
            }
        }
    }

    private function deleteAssets(string $targetDir, array $assetList): array
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
            $asset    = str_replace($targetDir, '', $image);
            $mime     = mime_content_type($image);
            $pathinfo = pathinfo($asset);

            if ($mime === 'image/jpeg') {
                $mime = 'image/jpg';
            }
            if (in_array($asset, $assetList) && strpos($mime, $pathinfo['extension']) !== false) {
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
            if (in_array($asset, $assetList)) {
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

        $files = $this->getDirContents($targetDir.'/files');

        foreach ($files as $video) {
            if (is_dir($video)) {
                $dirs[] = $video;
                continue;
            }
            $asset = str_replace($targetDir, '', $video);
            if (in_array($asset, $assetList)) {
                $notDeleted[] = $asset;
            } else {
                $filesystem->remove($video);
            }
        }
        foreach ($dirs as $dir) {
            if (file_exists($dir) && count(scandir($dir)) == 2) {
                $filesystem->remove($dir);
            }
        }
        return array_values(array_filter($notDeleted));
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
