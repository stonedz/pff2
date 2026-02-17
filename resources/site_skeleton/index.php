<?php

/**
 * Front controller
 *
 * @author paolo.fagni<at>gmail.com
 * @category lib
 * @version 0.1
 */

define('DS', DIRECTORY_SEPARATOR);
$rootDir = __DIR__;
$parentDir = dirname($rootDir);

if (file_exists($rootDir . DS . 'vendor' . DS . 'stonedz' . DS . 'pff2' . DS . 'bootstrap.php')) {
    define('ROOT', $rootDir);
    define('ROOT_LIB', ROOT . DS . 'vendor' . DS . 'stonedz' . DS . 'pff2');
} elseif (file_exists($rootDir . DS . 'bootstrap.php')) {
    define('ROOT', $rootDir);
    define('ROOT_LIB', ROOT);
} elseif (file_exists($parentDir . DS . 'vendor' . DS . 'stonedz' . DS . 'pff2' . DS . 'bootstrap.php')) {
    define('ROOT', $parentDir);
    define('ROOT_LIB', ROOT . DS . 'vendor' . DS . 'stonedz' . DS . 'pff2');
} elseif (file_exists($parentDir . DS . 'bootstrap.php')) {
    define('ROOT', $parentDir);
    define('ROOT_LIB', ROOT);
} else {
    define('ROOT', $rootDir);
    define('ROOT_LIB', ROOT);
}

define('CONTROLLERS', ROOT . DS . 'app' . DS . 'controllers');
define('PAGES', ROOT . DS . 'app' . DS . 'pages');
$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
$protocol = $isSecure ? 'https' : 'http';

$ext_root = $protocol . "://" . $_SERVER['HTTP_HOST'] . '/';

define('EXT_ROOT', $ext_root);
if (isset($_GET['url']) && isset($_GET['url'][0]) && $_GET['url'][0] == '/') {
    $_GET['url'] = substr($_GET['url'], 1);
}
(isset($_GET['url'])) ? $url = $_GET['url'] : $url = '';

require_once(ROOT_LIB . DS . 'bootstrap.php');
