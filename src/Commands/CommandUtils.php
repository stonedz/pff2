<?php

declare(strict_types=1);

/**
 * User: stonedz
 * Date: 2/2/15
 * Time: 3:33 PM
 */

namespace pff\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandUtils
{
    /**
     * Checks if the command exists
     *
     * @param string $cmd
     */
    public static function checkCommand(string $cmd): bool
    {
        $returnVal = shell_exec("which $cmd");
        return (empty($returnVal) ? false : true);
    }

    /**
     * Checks if deployment dir exists and create it otherwise
     */
    public static function checkDeployement(): bool
    {
        if (file_exists('deployement/php') && file_exists('deployement/nginx')) {
            return true;
        } else {
            mkdir('deployement/php', 0777, true);
            mkdir('deployement/nginx', 0777, true);
            return true;
        }
    }
}
