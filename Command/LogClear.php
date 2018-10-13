<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LogClear extends Command
{
    protected function configure()
    {
        $this
            ->setName('log:clear')
            ->setDescription('Delete debug.log file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = ROOT.'/var/log/debug.log';
        if (is_file($file)) {
            unlink($file);
            $output->writeln('<info>Log file deleted successfully.</info>');
        } else {
            $output->writeln('<info>Log file is not exists.</info>');
        }
    }
}