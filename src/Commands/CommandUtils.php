<?php
/**
 * User: stonedz
 * Date: 2/2/15
 * Time: 3:33 PM
 */

namespace pff\Commands;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandUtils {

    /**
     * Checks if the command exists
     *
     * @param string $command
     */
    public static function checkCommand($cmd) {
        $returnVal = shell_exec("which $cmd");
        return (empty($returnVal) ? false : true);
    }

    /**
     * Checks if deployement dir exists and create it otherwise
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public static function checkDeployement() {
        if(file_exists('deployement/php') &&  file_exists('deployement/nginx')) {
            return true;
        }
        else {
            mkdir('deployement/php', 0777, true);
            mkdir('deployement/nginx', 0777, true);
            return true;
        }
    }
}