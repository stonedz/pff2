<?php
/**
 * Front controller
 *
 * @author paolo.fagni<at>gmail.com
 * @category lib
 * @version 0.1
 */

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__. DS . '..'.DS.'..'.DS.'..'.DS.'..');
define('ROOT_LIB', __DIR__.DS.'..');
define('CONTROLLERS', __DIR__ . DS. '..'.DS.'..'.DS.'..'.DS.'..'.DS.'app'.DS.'controllers');
define('PAGES', __DIR__ . DS. '..'.DS.'..'.DS.'..'.DS.'..'.DS.'app'.DS.'pages');
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? "https" : "http";
$ext_root =  $protocol . "://" . $_SERVER['HTTP_HOST'].'/';

define('EXT_ROOT', $ext_root);

(isset($_GET['url'])) ? $url = $_GET['url'] : $url='' ;

require_once(ROOT_LIB  . DS . 'bootstrap.php');

