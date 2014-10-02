<?php
/** @codeCoverageIgnoreStart */

/**
 * Initializes includes.
 *
 * @author paolo.fagni<at>gmail.com
 */

//require_once (ROOT . DS . 'config' . DS . 'config.php');
require_once (ROOT_LIB . DS . 'lib' . DS . 'autoload.php');
//require_once (ROOT . DS . 'lib' .DS . 'vendor' . DS . '.composer' . DS. 'autoload.php');
require_once (ROOT . DS . 'app' . DS . 'autoload.php');
require_once (ROOT . DS . 'vendor' . DS . 'autoload.php');
require_once (ROOT_LIB . DS . 'lib' . DS . 'App.php');
require_once (ROOT_LIB . DS . 'lib' . DS . 'shared.php');
require_once (ROOT_LIB . DS . 'config' . DS . 'routes.php');

$app->run();
/** @codeCoverageIgnoreStop */