<?php
/** @codeCoverageIgnoreStart */

/**
 * Initializes includes.
 *
 * @author paolo.fagni<at>gmail.com
 */
require_once (ROOT . DS . 'vendor'. DS .'autoload.php');
require_once (ROOT_LIB  . DS . 'src' .DS. 'autoload.php');
require_once (ROOT . DS . 'app' . DS . 'autoload.php');
require_once (ROOT . DS . 'vendor' . DS . 'autoload.php');
require_once (ROOT_LIB . DS . 'src' . DS . 'App.php');
require_once (ROOT_LIB  . DS . 'shared.php');
require_once (ROOT . DS . 'app' . DS . 'config'. DS . 'routes.user.php');

$app->run();
/** @codeCoverageIgnoreStop */
