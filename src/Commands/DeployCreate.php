<?php
/**
 * User: stonedz
 * Date: 2/13/15
 * Time: 4:59 PM
 */

namespace pff\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class DeployCreate extends Command {

    protected function configure() {
        $this
            ->setName('deploy:create')
            ->setDescription('Creates a deployement configuration')
            ->addArgument(
                'profile-name',
                InputArgument::REQUIRED,
                'Deployement configuration name'
            )
            ->addOption(
                'exclude',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'exclude dirs (standard exclude list is built-in, only add new dirs/files)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        $possible_users = array('www-data', 'apache', 'ubuntu', 'root');
        $question = new Question('<question>Username:</question> ');
        $question->setAutocompleterValues($possible_users);
        $username = $questionHelper->ask($input, $output, $question);
        if(!$username) {
            $output->writeln('you need to enter a username!');
            return 1;
        }

        $question = new Question('<question>Host:</question> ');
        $host = $questionHelper->ask($input, $output, $question);
        if(!$host) {
            $output->writeln('you need to enter a host!');
            return 1;
        }

        $question = new Question('<question>Remote path:</question> ');
        $path = $questionHelper->ask($input, $output, $question);
        if(!$path) {
            $output->writeln('you need to enter a path!');
            return 1;
        }

        $question = new ConfirmationQuestion('<question>Always run deploy:optimize after publish? (y/n)</question> ', false);
        $optimize = $questionHelper->ask($input, $output, $question);

        $question = new ConfirmationQuestion('<question>Use sudo to change file permission on remote server? (y/n)</question> ', false);
        $use_sudo = $questionHelper->ask($input, $output, $question);

        $possible_groups = array('www-data', 'apache', 'httpd');
        $question = new Question('<question>Remote group:</question> ','www-data');
        $question->setAutocompleterValues($possible_groups);
        $remote_group = $questionHelper->ask($input, $output, $question);

        $question = new ConfirmationQuestion('<question>Ue a .pem file to publish? (y/n)</question> ', false);
        $use_pem = $questionHelper->ask($input, $output, $question);

        if($use_pem) {
            $question = new Question('<question>.pem file path (absolute or relative):</question> ');
            $pem_path = $questionHelper->ask($input, $output, $question);
        }
        else {
            $pem_path = false;
        }

        $custom_excludes = $input->getOption('exclude');
        $standard_excludes = array(
            ".git*",
            'app/public/files/*',
            'app/config/config.user.php',
            '.htaccess',
            'backups',
            'app/views/smarty/compiled_templates/*',
            'app/proxies/*',
            'app/public/admin/include/dbcommon.php',
            'app/public/admin/connections/ConnectionManager.php',
            '*.pem'
        );
        $excludes = array_merge($custom_excludes, $standard_excludes);

        $yamlDumper = new Dumper();
        $toDump = array(
            'username'               => $username,
            'host'                   => $host,
            'remote_dir'             => $path,
            'always_run_optimize'    => $optimize,
            'remote_group'           => $remote_group,
            'use_sudo'               => $use_sudo,
            'exclude'                => $excludes,
            'use_pem'                => $use_pem,
            'pem_path'               => $pem_path
        );

        $yaml = $yamlDumper->dump($toDump,2);

        $profile_name = $input->getArgument('profile-name');
        CommandUtils::checkDeployement();
        while(file_exists('deployement/publish_'.$profile_name.'.yml')) {
            $question = new ConfirmationQuestion('<question>deployement/publish_'.$profile_name.'.yml already exists, overwrite it?',false);
            $answer = $questionHelper->ask($input, $output, $question);
            if(!$answer) {
                $question = new Question('<question>New porfile name:</question> ');
                $profile_name = $questionHelper->ask($input, $output, $question);
            }
            else {
                break;
            }
        }
        if( file_put_contents('deployement/publish_'.$profile_name.'.yml', $yaml)) {
            $output->writeln('<info>Publish profile created at deployement/publish_'.$profile_name.'.yml</info>');
        }
        else {
            $output->writeln('<error>Error while writing output file!</error>');
        }
    }
}
