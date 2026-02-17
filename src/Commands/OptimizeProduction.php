<?php

/**
 * User: stonedz
 * Date: 2/5/15
 * Time: 12:25 PM
 */

namespace pff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeProduction extends Command
{
    protected function configure()
    {
        $this
            ->setName('deploy:optimize')
            ->setDescription('Generates doctrine proxies, clears doctrine caches and dump optimized composer autoload.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //check if doctrine executable extists
        if (!file_exists('cli-config.php')) {
            $output->writeln('<error>ERROR</error>');
        } else {
            $doctrine_executable = 'php cli-config.php';
        }

        $output->writeln('Optimizing environment for production...');

        $output->write('Clear doctrine metadata cache...');
        exec($doctrine_executable . ' orm:clear-cache:metadata', $res, $ret);
        if ($ret == 0) {
            $output->writeln('<info>DONE</info>');
        } else {
            $output->writeln('<error>ERROR</error>');
        }

        $output->write('Clear doctrine query cache...');
        exec($doctrine_executable . ' orm:clear-cache:query', $res, $ret);
        if ($ret == 0) {
            $output->writeln('<info>DONE</info>');
        } else {
            $output->writeln('<error>ERROR</error>');
        }

        $output->write('Clear and generate doctrine proxies...');
        exec('rm -rf app/proxies/*', $res, $ret);
        exec($doctrine_executable . ' orm:generate-proxies', $res, $ret);
        if ($ret == 0) {
            $output->writeln('<info>DONE</info>');
        } else {
            $output->writeln('<error>ERROR</error>');
        }

        $output->writeln('Generate optimized autoload php...');
        $command   = $this->getApplication()->find('composer:dumpautoload');
        $arguments = ['command' => 'composer:dumpautoload'];
        $inputa    = new ArrayInput($arguments);
        $ret       = $command->run($inputa, $output);
        return 0;
    }
}
