<?php

namespace pff\modules;

use pff\Abs\AModule;
use pff\Iface\IBeforeSystemHook;

/**
 * Module to manage sessions
 *
 * @author paolo.fagni<at>gmail.com
 */
class Session extends AModule implements IBeforeSystemHook
{
    /**
     * Executed before the system startup
     *
     * @return mixed
     */
    public function doBeforeSystem()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}
