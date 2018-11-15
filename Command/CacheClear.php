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
        $files = glob(ROOT.'/var/cache/*');
        
        if (empty($files)) {
            $output->writeln('<error>No file exists in cache folder.</error>');
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                if (! $fh = fopen($file, 'rb')) {
                    $output->writeln('<error>You haven\'t got a write permission to /var/cache/ folder.</error>');
                    die;
                }
                unlink($file);
                $output->writeln('<info>Cache file <comment>'.str_replace(ROOT, "", $file).'</comment> deleted successfully.</info>');
            }
        }
    }
}