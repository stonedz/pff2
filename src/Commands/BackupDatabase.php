<?php
/**
 * User: stonedz
 * Date: 2/2/15
 * Time: 3:09 PM
 */

namespace pff\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class BackupDatabase extends Command {

    protected function configure() {
        require ('app/config/config.user.php');
        $this
            ->setName('db:backupDb')
            ->setDescription('Backup the db')
            ->addOption(
                'backup-dir',
                'b',
                InputOption::VALUE_REQUIRED,
                'Backup directory',
                'backups/sql'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $questionHelper = $this->getHelper('question');

        if(!CommandUtils::checkCommand('mysqldump')) {
            $output->writeln('<error>Mysql client not found. Please install it!</error>');
            return;
        }

        require('app/config/config.user.php');
        $dev = $pffConfig['development_environment'];
        if($dev) {
            $dbUser = $pffConfig['databaseConfigDev']['user'];
            $dbHost = $pffConfig['databaseConfigDev']['host'];
            $dbName = $pffConfig['databaseConfigDev']['dbname'];
            $dbPass = $pffConfig['databaseConfigDev']['password'];
            if(isset($pffConfig['databaseConfigDev']['port'])) {
                $dbPort = $pffConfig['databaseConfigDev']['port'];
            }
            else {
                $dbPort = 3306;
            }
        }
        else {
            $dbUser = $pffConfig['databaseConfig']['user'];
            $dbHost = $pffConfig['databaseConfig']['host'];
            $dbName = $pffConfig['databaseConfig']['dbname'];
            $dbPass = $pffConfig['databaseConfig']['password'];
            if(isset($pffConfig['databaseConfig']['port'])) {
                $dbPort = $pffConfig['databaseConfig']['port'];
            }
            else {
                $dbPort = 3306;
            }
        }

        $backup_name = $dbName.'-BKP-'.date("dmY-Hi").'.sql';

        $backup_dir = $input->getOption('backup-dir');
        $output->writeln('Checking for backup dir...');
        if(!file_exists($backup_dir)) {
            $question = new ConfirmationQuestion('<question>Backup dir '.$backup_dir.' does not exists, create?</question>', 'n');
            if($questionHelper->ask($input, $output, $question)) {
                if(mkdir($backup_dir, 0755, true)) {
                    $output->writeln('<info>DONE</info>');
                }
                else{
                    $output->writeln('<error>ERROR</error>');
                    exit(1);
                }
            }
            else{
                $output->writeln('<error>ERROR</error>');
                exit(1);
            }
        }

        if (substr($backup_dir, -1) != '/') {
            $backup_dir .= '/';
        }

        $command = "mysqldump -u$dbUser -p$dbPass -h$dbHost -P$dbPort $dbName > $backup_dir$backup_name";

        $output->write('Generating db backup for $dbName...');
        exec($command, $res, $ret);
        if(0 == $ret) {
            $output->writeln('<info>DONE</info>');
            $output->writeln('<info>Backup written in '.$backup_dir.''.$backup_name.'</info>');
        }
        else {
            $output->writeln('<error>ERROR</error>');
            foreach($res as $line){
                $output->writeln($line);
            }
        }
    }
}