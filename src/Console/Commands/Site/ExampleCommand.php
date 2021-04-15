<?php

declare(strict_types=1);

namespace Flipsite\Console\Commands\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class ExampleCommand extends Command
{
    /**
     * @var string|null
     */
    protected static $defaultName = 'site:example';

    protected function configure() : void
    {
        $this->setDescription('Creates an example site');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io         = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        $siteFilename  = getenv('SITE_DIR').'/site.yaml';
        $themeFilename = getenv('SITE_DIR').'/theme.yaml';
        if (file_exists($siteFilename) || file_exists($themeFilename)) {
            $io->error('A site already exists.');
        } else {
            $filesystem->copy(__DIR__.'/site.yaml', $siteFilename);
            $filesystem->copy(__DIR__.'/theme.yaml', $themeFilename);
            $assetDir = getenv('SITE_DIR').'/assets';
            if (!is_dir($assetDir)) {
                $filesystem->mkdir($assetDir);
            }
            $filesystem->copy(__DIR__.'/flipsite.svg', $assetDir.'/flipsite.svg');
            $io->success('Example site created');
        }
        return 0;
    }
}
