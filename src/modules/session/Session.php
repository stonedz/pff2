<?php

namespace pff\modules;
use pff\Abstact\AModule;
use pff\Iface\IBeforeSystemHook;

/**
 * Module to manage sessions
 *
 * @author paolo.fagni<at>gmail.com
 */
class Session extends AModule implements IBeforeSystemHook {


    /**
     * Executed before the system startup
     *
     * @return mixed
     */
    public function doBeforeSystem() {
        session_start();
    }
}
