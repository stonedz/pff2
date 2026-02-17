<?php

declare(strict_types=1);

/**
 * User: stonedz
 * Date: 2/5/15
 * Time: 4:20 PM
 */

namespace pff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ComposerUpdate extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('composer:update')
            ->setDescription('Updates composer packages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find('composer:install');
        $arguments = ['command' => 'composer:install'];
        $inputa = new ArrayInput($arguments);
        $ret = $command->run($inputa, $output);


        $output->writeln('Upgrading composer packages...');

        if ($ret == 0) {
            passthru('php composer.phar update', $ret);
            $output->writeln('<info>DONE</info>');
        } else {
            $questionHelper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Composer not installed, do you want to install it</question> ', 'n');
            if (!$questionHelper->ask($input, $output, $question)) {
                return 1;
            } else {
                $command = $this->getApplication()->find('composer:install');
                $arguments = ['command' => 'composer:install'];
                $inputa = new ArrayInput($arguments);
                $ret = $command->run($inputa, $output);

                if ($ret == 0) {
                    $command = $this->getApplication()->find('composer:update');
                    $arguments = ['command' => 'composer:update'];
                    $inputa = new ArrayInput($arguments);
                    $ret = $command->run($inputa, $output);
                }
            }
        }
        return 0;
    }
}
