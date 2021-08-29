<?php
/**
 * User: paolo.fagni@gmail.com
 * Date: 09/11/14
 * Time: 10.30
 */

namespace pff\Core;

use Pimple\Container;

class ServiceContainer
{
    /**
     * @var Container
     */
    public static $pimple = null;

    public static function initPimple()
    {
        if (ServiceContainer::$pimple === null) {
            ServiceContainer::$pimple = new Container();
        }
    }

    public static function get($name)
    {
        return ServiceContainer::$pimple[$name];
    }

    public static function set()
    {
        return ServiceContainer::$pimple;
    }
}
