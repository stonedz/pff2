<?php

declare(strict_types=1);

/**
 * User: stonedz
 * Date: 2/5/15
 * Time: 4:56 PM
 */

namespace pff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerDumpAutoload extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('composer:dumpautoload')
            ->setDescription('Creates (dumps) composer autload')
            ->addOption(
                'no-optimize',
                null,
                InputOption::VALUE_NONE,
                'Dump composer autoload without optimizations'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find('composer:install');
        $arguments = ['command' => 'composer:install', '-c' => true];
        $inputa = new ArrayInput($arguments);
        $ret = $command->run($inputa, $output);

        if ($ret == 0) {
            $no_optimize = $input->getOption('no-optimize');

            if ($no_optimize) {
                passthru('php composer.phar dumpautoload', $ret);
            } else {
                passthru('php composer.phar dumpautoload -o', $ret);
            }
            if ($ret == 0) {
                $output->writeln('<info>DONE</info>');
            } else {
                $output->writeln('<error>ERROR</error>');
            }
        }
        return 0;
    }
}
