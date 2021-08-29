<?php
/**
 * User: stonedz
 * Date: 2/2/15
 * Time: 12:29 PM
 */

namespace pff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDatabase extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:updateDb')
            ->setDescription('Backups and then updates the db using doctrine orm:schema-tool:update --force')
            ->addOption(
                'no-backup',
                null,
                InputOption::VALUE_NONE
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'Mysql db port (used for db backup)',
                '0'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('no-backup')) {
            $mysql_port = $input->getOption('port');

            $command   = $this->getApplication()->find('db:backupDb');
            $arguments = ['command' => 'db:backupDb', '--port' => $mysql_port];
            $inputa    = new ArrayInput($arguments);
            $ret       = $command->run($inputa, $output);
        }

        exec('vendor/bin/doctrine orm:schema-tool:update --force', $res);
        foreach ($res as $r) {
            echo $r,"\n";
        }
    }
}
