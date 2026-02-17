<?php

declare(strict_types=1);

/**
 * User: paolo.fagni@gmail.com
 * Date: 09/11/14
 * Time: 10.30
 */

namespace pff\Core;

use Pimple\Container;
use pff\Config;

class ServiceContainer
{
    public static ?Container $pimple = null;

    public static function initPimple(): void
    {
        if (ServiceContainer::$pimple === null) {
            ServiceContainer::$pimple = new Container();
        }
    }

    public static function get(string $name): mixed
    {
        return ServiceContainer::$pimple[$name];
    }

    public static function set(): Container
    {
        return ServiceContainer::$pimple;
    }
}
