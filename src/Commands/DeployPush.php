<?php
/**
 * User: stonedz
 * Date: 2/13/15
 * Time: 4:59 PM
 */

namespace pff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser;

class DeployPush extends Command
{
    protected function configure()
    {
        $this
            ->setName('deploy:publish')
            ->setDescription('Publishes the site using the specified deployement configuration')
            ->addArgument(
                'profile-name',
                InputArgument::OPTIONAL,
                'Name of the profile (deployement/publish_[profile_name].yml, if not specified you\'ll chose one from all available profiles'
            )
            ->addOption(
                'dump-commands',
                null,
                InputOption::VALUE_NONE,
                'Only write to stdout the commands, do not execute them'
            )
            ->addOption(
                'full',
                null,
                InputOption::VALUE_NONE,
                'Publishes all the files, even the ones in ignore list'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        $profile_name = $input->getArgument('profile-name');
        if (!$profile_name || !file_exists('deployement/publish_'.$profile_name.'.yml')) {
            $output->writeln('<question>Specified profile does not exist or no profile specified</question>');

            $table = new Table($output);
            $table
                ->setHeaders(['ID', 'Profile Name']);

            $profiles_available = $this->getDeployementProfiles();
            foreach ($profiles_available as $k => $profile_name) {
                $table->addRow([$k, $profile_name]);
            }
            $table->render();

            $ok = false;
            while ($ok === false) {
                $question = new Question('<question>Please enter a profile ID or a profile name to use (leave blank to exit):</question> ', null);
                $user_choice = $questionHelper->ask($input, $output, $question);

                if ($user_choice === null) {
                    return 1;
                } elseif (is_numeric($user_choice) && count($profiles_available)>$user_choice) {
                    $profile_name = $profiles_available[$user_choice];
                    $ok = true;
                } elseif (in_array($user_choice, $profiles_available)) {
                    $profile_name = $user_choice;
                    $ok = true;
                } else {
                    $output->writeln('<error>Wrong profile!</error>');
                }
            }
        }

        return $this->publish($input, $output, $profile_name);
    }

    /**
     * Publish a site using
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $profile_name
     */
    protected function publish(InputInterface $input, OutputInterface $output, $profile_name):int
    {
        $output->writeln('<info>Publishing to '.$profile_name.'...</info>');
        $parser = new Parser();
        $profile_config = $parser->parse(file_get_contents('deployement/publish_'.$profile_name.'.yml'));

        if (!CommandUtils::checkCommand('rsync')) {
            $output->writeln('<error>rsync not installed, please install it. Exiting now...</error>');
            return 1;
        }
        if ($profile_config['use_pem']) {
            $command = 'rsync -rltDvze "ssh -i '.$profile_config['pem_path'].'" ';
        } else {
            $command = 'rsync -rltDvz ';
        }

        $is_full = $input->getOption('full');
        if (!$is_full) {
            foreach ($profile_config['exclude'] as $exclude) {
                $command .= '--exclude \'' . $exclude . '\' ';
            }
        }

        if (substr($profile_config['remote_dir'], -1, 1) != '/') {
            $profile_config['remote_dir'] .= '/';
        }

        $command .= '. '.$profile_config['username'].'@'.$profile_config['host'].':'.$profile_config['remote_dir'];


        $chmod = [
            'chown -R '.$profile_config['username'].':'.$profile_config['remote_group'].' '.$profile_config['remote_dir'],
            'chmod -R 750 '.$profile_config['remote_dir'],
            'chmod -R 770 '.$profile_config['remote_dir'].'app/logs',
            'chmod -R 770 '.$profile_config['remote_dir'].'app/proxies',
            'chmod -R 770 '.$profile_config['remote_dir'].'app/public',
            'chmod -R 770 '.$profile_config['remote_dir'].'app/tmp',
            'chmod -R 770 '.$profile_config['remote_dir'].'tmp',
        ];

        $permissions_commands = 'ssh '
            .($profile_config['use_pem'] ? '-i '.$profile_config['pem_path'] : '')
            .' '
            .$profile_config['username']
            .'@'
            .$profile_config['host']
            .' '
            .($profile_config['use_sudo'] ? 'sudo' : '')
            .' \'';
        foreach ($chmod as $c) {
            $permissions_commands .= $c. ' && ';
        }
        $permissions_commands = substr($permissions_commands, 0, -3);
        $permissions_commands .= '\'';

        // PUBLISH
        $dump_commands = $input->getOption('dump-commands');
        if ($dump_commands) {
            $output->writeln($command);
        } else {
            $output->writeln('Publishing with rsync, please wait (it may take a while)...');
            passthru($command);
            $output->writeln('<info>PUBLISH DONE</info>');
        }

        // OPTIMIZE
        $run_optimize = $profile_config['always_run_optimize'];
        if ($run_optimize) {
            $output->writeln('Run optimizations...');
            $command = 'ssh '
                .($profile_config['use_pem'] ? '-i '.$profile_config['pem_path'] : '')
                .' '
                .$profile_config['username']
                .'@'
                .$profile_config['host']
                .' '
                .'\'cd '.$profile_config['remote_dir'].' && vendor/bin/pff deploy:optimize\'';

            if ($dump_commands) {
                $output->writeln($command);
            } else {
                passthru($command);
            }
            $output->writeln('<info>OPTIMIZE DONE</info>');
        }

        // PERMISSIONS
        $output->writeln('Setting permissions...');
        if ($dump_commands) {
            $output->writeln($permissions_commands);
        } else {
            passthru($permissions_commands);
        }
        $output->writeln('<info>PERMISSIONS DONE</info>');
         return 0;
    }

    protected function getDeployementProfiles()
    {
        $profiles = glob('deployement/publish_*.yml');
        $res = [];
        foreach ($profiles as $p) {
            $res[] = substr(substr(strstr($p, '_'), 0, -4), 1);
        }
        return $res;
    }
}
