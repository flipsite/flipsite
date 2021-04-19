<?php

declare(strict_types=1);

namespace Flipsite\Console\Commands\Site;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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

        $question = new Question('Please enter the name of your site: ', );

        $helper = $this->getHelper('question');
        $name   = $helper->ask($input, $output, $question);

        $siteFilename   = getenv('SITE_DIR').'/site.yaml';
        $themeFilename  = getenv('SITE_DIR').'/theme.yaml';
        $readmeFilename = getenv('SITE_DIR').'/README.md';
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
            $readme = file_get_contents(__DIR__.'/README.md');
            $readme = str_replace('{{name}}', $name, $readme);
            file_put_contents($readmeFilename, $readme);

            $io->success('Example site created for '.$name);
        }
        return 0;
    }
}
