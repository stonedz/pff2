<?php
/**
 * User: stonedz
 * Date: 1/29/15
 * Time: 3:49 PM
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
use Symfony\Component\Yaml\Dumper;
use Guzzle\Stream\Stream;

class GenerateFigFiles extends Command
{
    /**
     * Name of the fig file used for dev
     *
     * @var string
     */
    private $fig_dev = 'fig.yml';

    /**
     * Name of the fig file used for production
     *
     * @var string
     */
    private $fig_prod = 'fig_prod.yml';

    /**
     * Host for the db for dev
     *
     * @var string
     */
    private $db_host_dev;

    /**
     * Host for the db for prod
     *
     * @var string
     */
    private $db_host_prod;

    protected function configure()
    {
        require('app/config/config.user.php');
        $this->db_host_dev  = $pffConfig['databaseConfigDev']['host'];
        $this->db_host_prod = $pffConfig['databaseConfig']['host'];

        $this
            ->setName('docker:generateFigFiles')
            ->setDescription('generates fig.yml and fig_prod.yml files (if they don\'t exist)')
            ->addArgument(
                'app-name',
                InputArgument::REQUIRED,
                'app name, used to name your containers'
            )
            ->addOption(
                'create-production',
                'C',
                InputOption::VALUE_NONE,
                'Creates fig_prod.yml'
            )
            ->addOption(
                'virtualhost',
                'B',
                InputOption::VALUE_REQUIRED,
                'Virtualhost name for production webserver using a reverse proxy (PROD)',
                'prod.site.com'
            )
            ->addOption(
                'web-port',
                'W',
                InputOption::VALUE_REQUIRED,
                'Host post where to bind the webserver (PRODUCTION)',
                8080
            )
            ->addOption(
                'db-host',
                'K',
                InputOption::VALUE_REQUIRED,
                'Host of the db for the web docker (to be used in config.user.php) (PRODUCTION)',
                $this->db_host_prod
            )
            ->addOption(
                'db-port',
                'D',
                InputOption::VALUE_REQUIRED,
                'Host post where to bind the mariaDb (PRODUCTION)',
                3306
            )
            ->addOption(
                'db-host-dev',
                'k',
                InputOption::VALUE_REQUIRED,
                'Host of the db for the web docker (to be used in config.user.php) (DEV)',
                $this->db_host_dev
            )
            ->addOption(
                'db-port-dev',
                'd',
                InputOption::VALUE_REQUIRED,
                'Host post where to bind the mariaDb (DEV)',
                3306
            )
            ->addOption(
                'phpmyadmin-port-dev',
                'p',
                InputOption::VALUE_REQUIRED,
                'Host post where to bind the phpmyadmin (DEV)',
                8008
            )
            ->addOption(
                'web-port-dev',
                'w',
                InputOption::VALUE_REQUIRED,
                'Host post where to bind the webserver (DEV)',
                8080
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $questionHelper = $this->getHelper('question');

        if (!CommandUtils::checkCommand('docker') && !CommandUtils::checkCommand('docker.io')) {
            $output->writeln('<error>Docker does not seem to be installed, please install it!</error>');
            $question = new ConfirmationQuestion('<question>Continue anyway?</question> ', 'n');

            if (!$questionHelper->ask($input, $output, $question)) {
                return 0;
            }
            return 0;
        }

        CommandUtils::checkDeployement();

        $web_port        = $input->getOption('web-port-dev');
        $db_port         = $input->getOption('db-port-dev');
        $db_host         = $input->getOption('db-host-dev');
        $phpmyadmin_port = $input->getOption('phpmyadmin-port-dev');

        $this->askForFile('dev-php.ini', $input, $output, $questionHelper);
        $this->askForFileNginx('dev-nginx.conf', $input, $output, $questionHelper);

        if (!file_exists($this->fig_dev)) {
            $output->write('Checking for nginx error log in app/logs/docker-logs/ ...');
            if (!file_exists('app/logs/docker-logs')) {
                mkdir('app/logs/docker-logs', 0777, true);
            }
            if (!file_exists('app/logs/docker-logs/nginx-error.log')) {
                touch('app/logs/docker-logs/nginx-error.log');
            }
            $output->writeln('<info>DONE</info>');

            $output->write('Generating fig.yml for developement...');
            $toBeWritten =
"web:
  image: neropaco/docker-lamp-dev
  ports:
    - \"$web_port:80\"
  links:
    - db:".$db_host."
  volumes:
    - .:/srv/http
    - ./deployement/php/dev-php.ini:/etc/php5/fpm/php.ini
    - ./deployement/nginx/dev-nginx.conf:/etc/nginx/nginx.conf
    - ./app/logs/docker-logs/nginx-error.log:/var/log/nginx/error.log

db:
  image: tutum/mariadb:10.1
  ports:
    - \"$db_port:3306\"

phpmyadmin:
  image: maxexcloo/phpmyadmin
  ports:
    - \"$phpmyadmin_port:80\"
  links:
    - db:mariadb";
            if (file_put_contents($this->fig_dev, $toBeWritten)) {
                $output->writeln('<info>DONE</info>');
            } else {
                $output->writeln('<error>ERROR</error>');
            }
        } else {
            $output->writeln('<comment>'.$this->fig_dev.' file already exists. Delete it or move it to recreate the file</comment>');
        }

        if ($input->getOption('create-production') != 1) {
            return 0;
        }

        $this->askForFile('prod-php.ini', $input, $output, $questionHelper);
        $this->askForFileNginx('prod-nginx.conf', $input, $output, $questionHelper);

        if (!file_exists($this->fig_prod)) {
            $output->write('Generating fig_prod.yml for production...');

            $db_host      = $input->getOption('db-host');
            $web_port     = $input->getOption('web-port');
            $db_port      = $input->getOption('db-port');
            $virtualhost  = $input->getOption('virtualhost');
            $app_name     = $input->getArgument('app-name');

            $toBeWritten =
"web:
  image: stonedz/docker-lamp:latest
  links:
    - db:".$db_host."
  volumes:
    - .:/srv/http
    - ./deployement/php/prod-php.ini:/etc/php5/fpm/php.ini
    - ./deployement/nginx/prod-nginx.conf:/etc/nginx/nginx.conf
  ports:
    - \"$web_port:80\"
  environment:
    VIRTUAL_HOST: $virtualhost

db:
  image: tutum/mariadb:10.1
  volumes_from:
    - ".$app_name."_db_data
  ports:
    - \"$db_port:3306\"";

            if (file_put_contents($this->fig_prod, $toBeWritten)) {
                $output->writeln('<info>DONE</info>');
            } else {
                $output->writeln('<error>ERROR</error>');
            }

            $question = new ConfirmationQuestion('<question>The database in production will use a separate volume data container to maintain data, it is called '.$app_name.'_db_data, do you want me to try to locate it and create it? </question>', 'n');
            if ($questionHelper->ask($input, $output, $question)) {
                $this->create_db_volume_data($input, $output, $app_name.'_db_data', $questionHelper);
            } else {
                return 0;
            }
        } else {
            $output->writeln('<comment>'.$this->fig_prod.' file already exists. Delete it or move it to recreate the file</comment>');
        }
    }

    protected function create_db_volume_data(InputInterface $input, OutputInterface $output, $container_name, QuestionHelper $question_helper)
    {
        if (CommandUtils::checkCommand('docker')) {
            $command = 'docker';
        } elseif (CommandUtils::checkCommand('docker.io')) {
            $command = 'docker.io';
        } else {
            $output->writeln('<error>Can\'t find docker executable.</error>');
            return;
        }

        $question = new ConfirmationQuestion('<question>Do you want to use sudo? </question>', 'n');
        if ($question_helper->ask($input, $output, $question)) {
            $command = 'sudo '. $command;
        }

        $comman_create = $command.' run -v /var/lib/mysql --name '.$container_name.' busybox true';

        exec($comman_create, $res, $ret);
        if (0 == $ret) {
            $output->writeln('<info>Db data volume container created.</info>');
        } else {
            $output->writeln('<error>Cannot create db data volume container using the following command: </error>');
            $output->writeln('<error>'.$comman_create.'</error>');
        }
    }

    protected function getPHPIniFiles($fileName)
    {
        $original = 'https://github.com/stonedz/dev-config/raw/master/'.$fileName;
        $dest = 'deployement/php/'.$fileName;
        copy($original, $dest);
        chmod($dest, 775);
    }

    /**
     * @param string $fileName
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $questionHelper
     * @return bool
     */
    protected function askForFile($fileName, InputInterface $input, OutputInterface $output, $questionHelper)
    {
        $output->write('Checking for '.$fileName.'...');
        if (!file_exists('deployement/php/'.$fileName)) {
            $this->getPHPIniFiles($fileName);
        } else {
            $question = new ConfirmationQuestion('<question>'.$fileName.' file already present, overwrite?</question>', 'n');
            if ($questionHelper->ask($input, $output, $question)) {
                $this->getPHPIniFiles($fileName);
            }
        }
        $output->writeln('<info>DONE</info>');

        return true;
    }

    protected function getNginxFiles($fileName)
    {
        $original = 'https://github.com/stonedz/dev-config/raw/master/'.$fileName;
        $dest = 'deployement/nginx/'.$fileName;
        copy($original, $dest);
        chmod($dest, 775);
    }
    /**
     * @param string $fileName
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $questionHelper
     * @return bool
     */
    protected function askForFileNginx($fileName, InputInterface $input, OutputInterface $output, $questionHelper)
    {
        $output->write('Checking for '.$fileName.'...');
        if (!file_exists('deployement/nginx/'.$fileName)) {
            $this->getNginxFiles($fileName);
        } else {
            $question = new ConfirmationQuestion('<question>'.$fileName.' file already present, overwrite?</question>', 'n');
            if ($questionHelper->ask($input, $output, $question)) {
                $this->getNginxFiles($fileName);
            }
        }
        $output->writeln('<info>DONE</info>');

        return true;
    }
}
