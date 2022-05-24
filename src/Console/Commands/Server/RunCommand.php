<?php

declare(strict_types=1);
namespace Flipsite\Console\Commands\Server;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RunCommand extends Command
{
    /**
     * @var string|null
     */
    protected static $defaultName = 'server:run';

    protected function configure() : void
    {
        $this->setDescription('Run local dev server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io   = new SymfonyStyle($input, $output);
        $host = '127.0.0.1';
        $port = $this->getPort($host);
        if ($port) {
            $io->success('Server listening on '.$host.':'.$port);
            $io->writeln('// Quit the server with CONTROL-C.');
            exec('cd vendor/flipsite/flipsite; php -S '.$host.':'.$port.' router.php');
        } else {
            $io->error('Could not start server, too many running');
        }
        return 0;
    }

    private function getPort(string $host) : string|bool
    {
        for ($i = 0; $i <= 9; $i++) {
            $port    = '800'.$i;
            $content = @file_get_contents('http://'.$host.':'.$port);
            if (!$content) {
                return $port;
            }
        }
        return false;
    }
}
