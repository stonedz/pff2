<?php
/**
 * Front controller
 *
 * @author paolo.fagni<at>gmail.com
 * @category lib
 * @version 0.1
 */

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__);
define('ROOT_LIB', __DIR__.DS.'vendor'.DS.'stonedz'.DS.'pff2');
define('CONTROLLERS', __DIR__ . DS. 'app'.DS.'controllers');
define('PAGES', __DIR__  .DS.'app'.DS.'pages');

$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
$protocol = $isSecure ? 'https' : 'http';

$ext_root =  $protocol . "://" . $_SERVER['HTTP_HOST'].'/';

define('EXT_ROOT', $ext_root);
if(isset($_GET['url']) && $_GET['url'][0] == '/') {
    $_GET['url'] = substr($_GET['url'],1);
}
(isset($_GET['url'])) ? $url = $_GET['url'] : $url='' ;

require_once(ROOT_LIB  . DS . 'bootstrap.php');

