<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClear extends Command
{
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clear application cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file  = ROOT.'/var/cache/config.php';
        if (is_file($file)) {
            unlink($file);
            $output->writeln('<info>Cache file deleted successfully.</info>');
        } else {
            $output->writeln('<error>Cache file is not exists.</error>');
        }
    }
}