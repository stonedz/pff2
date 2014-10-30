<?php

namespace pff\Core;
use pff\Exception\HelperException;

/**
 * Manages helpers
 *
 * @author paolo.fagni<at>gmail.com
 */
class HelperManager
{

    /**
     * Load an helper file
     *
     * @param string $helperName Name of the helper to include
     * @return bool
     * @throws HelperException
     */
    public function load($helperName) {
        $helperFilePathUser = ROOT . DS . 'app' . DS . 'helpers' . DS . $helperName . '.php';
        $helperFilePathPff  = ROOT_LIB . DS . 'src' . DS . 'helpers' . DS . $helperName . '.php';

        $found = false;

        if (file_exists($helperFilePathUser)) {
            include_once($helperFilePathUser);
            $found = true;
        }
        if (file_exists($helperFilePathPff)) {
            include_once($helperFilePathPff);
            $found = true;
        }

        if (!($found)) {
            throw new HelperException("Helper not found: " . $helperName);
        }
        else {
            return true;
        }
    }

}
