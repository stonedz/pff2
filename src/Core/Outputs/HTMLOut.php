<?php
/**
 * User: paolo.fagni@gmail.com
 * Date: 07/11/14
 * Time: 11.25
 */

namespace pff\Core\Outputs;


use pff\Iface\IOutputs;

class HTMLOut implements IOutputs{

    public function outputHeader() {
        return true;
    }
}