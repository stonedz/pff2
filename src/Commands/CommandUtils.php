<?php
/**
 * User: stonedz
 * Date: 2/2/15
 * Time: 3:33 PM
 */

namespace pff\Commands;


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
}