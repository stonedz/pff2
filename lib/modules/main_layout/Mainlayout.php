<?php
namespace pff\modules;
/**
 * Created by IntelliJ IDEA.
 * User: stonedz
 * Date: 8/20/14
 * Time: 3:24 PM
 * To change this template use File | Settings | File Templates.
 */

class Mainlayout extends \pff\AModule implements \pff\IBeforeHook{

    /**
     * Executes actions before the Controller
     *
     * @return mixed
     */
    public function doBefore() {
        $l = \pff\FLayout::create('main_layout.php', $this->_app);
        $this->_controller->setLayout($l);
    }
}