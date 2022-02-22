<?php

declare(strict_types=1);

namespace Flipsite\Console\Commands\Compile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class CompileCommand extends Command
{
    /**
     * @var string|null
     */
    protected static $defaultName = 'compile';

    protected function configure() : void
    {
        $this->setDescription('Compiles a static version of the site');
        // $this->addArgument('target', InputArgument::REQUIRED, 'Path to target folder');
        // $this->addArgument('domain', InputArgument::REQUIRED, 'Static site domain (https://domain.com)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        putenv('APP_BASEPATH=');
        putenv('APP_SERVER=');

        $enviroment = new \Flipsite\Enviroment();
        $reader     = new \Flipsite\Data\Reader($enviroment);
        $slugs      = $reader->getSlugs();
        $allPages   = array_keys($slugs->getAll());
        $allPages = array_slice($allPages,0,3);

        $options = $reader->get('static');
        $targetDir = $options['target'] ?? 'static';
        if (!str_starts_with($targetDir,'/')) {
            $targetDir = $enviroment->getSiteDir().'/'.$targetDir;
        }
        if (!is_dir($targetDir)) {
            mkdir($targetDir);
        }
        $targetDir = rtrim($targetDir,'/');
        $targetDir = realpath($targetDir);
        $https = $options['https'] ?? true;
        $domain = $options['domain'] ?? 'flipsite.io';

        $this->deleteOld($targetDir);

        foreach ($allPages as $page) {
            $html = $this->getResponse($https, $domain, $page);
            $output->writeln($this->writeFile($targetDir,$page.'/index.html',$html));
            $assets = $this->parseAssets($html);
            // Page assets
            foreach ($assets as $asset) {
                $source = $this->getResponse($https, $domain, $asset);
                $output->writeln($this->writeFile($targetDir, $asset, $source));
            }
        }

        return 0;
    }
    private function deleteOld(string $targetDir) : void
    {
        $filesystem = new Filesystem();
        $files      = $this->getDirContents($targetDir);
        $filesystem->remove($targetDir.'/img');
        $filesystem->remove($targetDir.'/videos');
        foreach ($files as $file) {
            if (false !== mb_strpos($file, 'index.html')) {
                $filesystem->remove($file);
            }
        }
    }
    private function getResponse(bool $https, string $domain, string $page) : string {
        ob_start();
        $_SERVER['REQUEST_URI'] = str_replace('//','/','/'.$page);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        if ($https) {
            $_SERVER['SERVER_PORT'] = 443;
            $_SERVER['HTTPS'] = 'on';
        } else {
            $_SERVER['SERVER_PORT'] = 80;
            unset($_SERVER['HTTPS']);
        }
        $_SERVER['HTTP_HOST'] = $domain;
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/98.0.4758.80 Safari/537.36';
        include __DIR__.'/../../../index.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    private function writeFile(string $targetDir, string $page, string $html) : string {
        $filename = $targetDir.'/'.$page;
        $filename = str_replace('//', '/', $filename);
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }
        file_put_contents($filename, $html);
        return '+ '.$filename;
    }
    private function parseAssets(string $html) : array {
        $assets = [];

        $html = str_replace("\n", '', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = str_replace('> <', '><', $html);
        $html = str_replace('> ', '>', $html);
        $html = str_replace(' <', '<', $html);

        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($html);

        // $sourceTags = $doc->getElementsByTagName('source');
        // foreach ($sourceTags as $tag) {
        //     $tmp = explode(', ', $tag->getAttribute('srcset'));
        //     foreach ($tmp as $t) {
        //         $tmp2     = explode(' ', $t);
        //         $images[] = $tmp2[0];
        //     }
        // }
        $imgTags = $doc->getElementsByTagName('img');
        foreach ($imgTags as $tag) {
            $assets[] = $tag->getAttribute('src');
        }
        return array_unique($assets);
    }

            // $metaTags = $doc->getElementsByTagName('meta');
            // foreach ($metaTags as $tag) {
            //     if ('og:image' === $tag->getAttribute('property')) {
            //         $url      = $tag->getAttribute('content');
            //         $url      = str_replace($domain, '', $url);
            //         $images[] = $url;
            //     }
            // }

            // $linkTags = $doc->getElementsByTagName('link');
            // foreach ($linkTags as $tag) {
            //     $url      = $tag->getAttribute('href');
            //     $pathinfo = pathinfo($url);
            //     if (in_array($pathinfo['extension'] ?? '', ['ico', 'svg', 'png'])) {
            //         $url      = str_replace($domain, '', $url);
            //         $images[] = $url;
            //     }
            // }
        //return $html = str_replace("\n", '', $html);
    //         $html = preg_replace('/\s+/', ' ', $html);
    //         $html = str_replace('> <', '><', $html);
    //         $html = str_replace('> ', '>', $html);
    //         $html = str_replace(' <', '<', $html);

    //         libxml_use_internal_errors(true);
    //         $doc = new \DOMDocument();
    //         $doc->loadHTML($html);
    //         $sourceTags = $doc->getElementsByTagName('source');
    //         foreach ($sourceTags as $tag) {
    //             $tmp = explode(', ', $tag->getAttribute('srcset'));
    //             foreach ($tmp as $t) {
    //                 $tmp2     = explode(' ', $t);
    //                 $images[] = $tmp2[0];
    //             }
    //         }
    //         $imgTags = $doc->getElementsByTagName('img');
    //         foreach ($imgTags as $tag) {
    //             $images[] = $tag->getAttribute('src');
    //         }

    //         $metaTags = $doc->getElementsByTagName('meta');
    //         foreach ($metaTags as $tag) {
    //             if ('og:image' === $tag->getAttribute('property')) {
    //                 $url      = $tag->getAttribute('content');
    //                 $url      = str_replace($domain, '', $url);
    //                 $images[] = $url;
    //             }
    //         }

    //         $linkTags = $doc->getElementsByTagName('link');
    //         foreach ($linkTags as $tag) {
    //             $url      = $tag->getAttribute('href');
    //             $pathinfo = pathinfo($url);
    //             if (in_array($pathinfo['extension'] ?? '', ['ico', 'svg', 'png'])) {
    //                 $url      = str_replace($domain, '', $url);
    //                 $images[] = $url;
    //             }
    //         }
    //     }

    //}
    //     $io = new SymfonyStyle($input, $output);
    //     putenv('IMG_DIR='.__DIR__.'/img');
    //     putenv('APP_BASEPATH=');
    //     putenv('APP_SERVER=http://127.0.0.1:8000');
    //     $enviroment = new \Flipsite\Enviroment();
    //     $reader     = new \Flipsite\Data\Reader($enviroment);
    //     $slugs      = $reader->getSlugs();
    //     $allPages   = array_keys($slugs->getAll());

    //     $targetFolder = $input->getArgument('target');
    //     if (!is_dir($targetFolder)) {
    //         $io->error('Folder '.$targetFolder.' not found');
    //         return 0;
    //     }

    //     $domain = $input->getArgument('domain');
    //     if (0 !== mb_strpos($domain, 'http')) {
    //         $io->error('Domain should start with http or https.');
    //         return 0;
    //     }
    //     $domain = trim($domain, '/');

    //     // Delete
    //     $filesystem = new Filesystem();
    //     $files      = $this->getDirContents($targetFolder);
    //     $filesystem->remove($targetFolder.'/img');
    //     foreach ($files as $file) {
    //         if (false !== mb_strpos($file, 'index.html')) {
    //             $filesystem->remove($file);
    //         }
    //     }
    //     $files = $this->getDirContents($targetFolder);
    //     foreach ($files as $file) {
    //         if (is_dir($file) && 0 === count(glob($file.'/*'))) {
    //             $filesystem->remove($file);
    //         }
    //     }

    //     $images = [];
    //     foreach ($allPages as $page) {
    //         $url      = trim($enviroment->getServer().'/'.$page, '/');
    //         $html     = file_get_contents($url);
    //         $html     = str_replace('http://127.0.0.1:8000', $domain, $html);
    //         $filename = str_replace('//', '/', $targetFolder.'/'.$page.'/index.html');
    //         $filename = str_replace('//', '/', $filename);
    //         if (!file_exists(dirname($filename))) {
    //             mkdir(dirname($filename));
    //         }
    //         file_put_contents($filename, $html);
    //         $output->writeln('+ '.$filename);

    //         $html = str_replace("\n", '', $html);
    //         $html = preg_replace('/\s+/', ' ', $html);
    //         $html = str_replace('> <', '><', $html);
    //         $html = str_replace('> ', '>', $html);
    //         $html = str_replace(' <', '<', $html);

    //         libxml_use_internal_errors(true);
    //         $doc = new \DOMDocument();
    //         $doc->loadHTML($html);
    //         $sourceTags = $doc->getElementsByTagName('source');
    //         foreach ($sourceTags as $tag) {
    //             $tmp = explode(', ', $tag->getAttribute('srcset'));
    //             foreach ($tmp as $t) {
    //                 $tmp2     = explode(' ', $t);
    //                 $images[] = $tmp2[0];
    //             }
    //         }
    //         $imgTags = $doc->getElementsByTagName('img');
    //         foreach ($imgTags as $tag) {
    //             $images[] = $tag->getAttribute('src');
    //         }

    //         $metaTags = $doc->getElementsByTagName('meta');
    //         foreach ($metaTags as $tag) {
    //             if ('og:image' === $tag->getAttribute('property')) {
    //                 $url      = $tag->getAttribute('content');
    //                 $url      = str_replace($domain, '', $url);
    //                 $images[] = $url;
    //             }
    //         }

    //         $linkTags = $doc->getElementsByTagName('link');
    //         foreach ($linkTags as $tag) {
    //             $url      = $tag->getAttribute('href');
    //             $pathinfo = pathinfo($url);
    //             if (in_array($pathinfo['extension'] ?? '', ['ico', 'svg', 'png'])) {
    //                 $url      = str_replace($domain, '', $url);
    //                 $images[] = $url;
    //             }
    //         }
    //     }
    //     $images = array_unique($images);
    //     foreach ($images as $image) {
    //         $filename = $targetFolder.$image;
    //         $filename = str_replace('//', '/', $filename);
    //         if (!file_exists(dirname($filename))) {
    //             mkdir(dirname($filename));
    //         }
    //         $url = trim($enviroment->getServer().$image);
    //         file_put_contents($filename, file_get_contents($url));
    //         $output->writeln('+ '.$filename);
    //     }

    //     // Sitemap
    //     $sitemapUrl = trim($enviroment->getServer().'/sitemap.xml');
    //     $sitemap    = file_get_contents($sitemapUrl);
    //     $sitemap    = str_replace('http://127.0.0.1:8000', $domain, $sitemap);
    //     $filename   = $targetFolder.'/sitemap.xml';
    //     $filename   = str_replace('//', '/', $filename);
    //     file_put_contents($filename, $sitemap);
    //     $output->writeln('+ '.$filename);

    //     // Robots
    //     $robots   = new \Flipsite\Utils\Robots(true, $enviroment->getServer());
    //     $filename = $targetFolder.'/robots.txt';
    //     $filename = str_replace('//', '/', $filename);
    //     $robots   = str_replace('http://127.0.0.1:8000', $domain, (string) $robots);
    //     file_put_contents($filename, $robots);
    //     $output->writeln('+ '.$filename);

    //     $io->success('Static site created');
    //     return 0;
    // }

    private function getDirContents($dir, &$results = [])
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
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