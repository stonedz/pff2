<?php
/**
 * User: stonedz
 * Date: 2/5/15
 * Time: 3:32 PM
 */

namespace pff\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerInstall extends Command {

    protected function configure() {
        $this
            ->setName('composer:install')
            ->setDescription('Checks if composer is installed and install it if not. It also updates composer.')
            ->addOption(
                'only-check',
                'c',
                InputOption::VALUE_NONE,
                'Only checks if composer is installed'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $only_chekc = $input->getOption('only-check');

        if(file_exists('composer.phar')) {
            if($only_chekc) {
                $output->writeln('<info>Composer installed</info>');
                return 0;
            }
            $output->write('Composer already installed, upgrading it...');
            exec('php composer.phar selfupdate', $res, $ret);
            if($ret == 0) {
               $output->writeln('<info>DONE</info>');
            }
            else {
                $output->writeln('<error>ERROR</error>');
                return 1;
            }
        }
        else {
            if($only_chekc) {
                $output->writeln('<error>Composer not installed</error>');
                return 1;
            }
            $output->write('Installing composer...');
            exec('php -r "readfile(\'https://getcomposer.org/installer\');" | php', $res, $ret);
            if($ret == 0) {
                $output->writeln('<info>DONE</info>');
                return 0;
            }
            else {
                $output->writeln('<error>ERROR - please install composer manually (https://getcomposer.org/download/)</error>');
                return 1;
            }
        }
    }
}