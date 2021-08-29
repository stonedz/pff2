<?php

namespace pff\modules;

use pff\Abs\AModule;
use pff\Factory\FLayout;
use pff\Iface\IBeforeHook;

/**
 * User: paolo.fagni@gmail.com
 * Date: 8/20/14
 * Time: 3:24 PM
 */

class Mainlayout extends AModule implements IBeforeHook
{
    /**
     * Executes actions before the Controller
     *
     * @return mixed
     */
    public function doBefore()
    {
        if (count($this->_controller->getViews()) == 0) {
            $l = FLayout::create('main_layout.php', $this->_app);
            $this->_controller->setLayout($l);
        }
    }
}
