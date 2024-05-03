<?php

/**
 * App configuration file
 *
 * @author paolo.fagni<at>gmail.com
 */

/*
 * The application name
 * CHANGE THIS, IT USED AS A KEY PREFIX FOR APC CACHE
 */
$pffConfig['app_name'] = 'My new app';

/*
 * Application base path.
 *
 * If your application is installed in a subdirectory and not in your site root
 * enter the application path with a TRAILING SLASH. For example 'path/to/application/'
 * If the developmente_environment is set to true the base_path_dev will be used.
 */
$pffConfig['base_path'] = '';
$pffConfig['base_path_dev'] = '';

/*
 * Set to true if in DEBUG mode
 */
$pffConfig['development_environment'] = true;

/*
 * Dafault controller action
 */
$pffConfig['default_action'] = 'index';

/**
 * Show deprecated, notice and strict errors. ONLY WORKS IN DEVELOPMENT MODE
 */
$pffConfig['show_all_errors'] = true;

///////////////////////////////////////
// Database
///////////////////////////////////////

/*
 * Set to false if you DON'T WANT Doctrine ORM module to be loaded.
 */
$pffConfig['orm'] = true;

/*
 * Db connection data.
 */
$pffConfig['databaseConfig'] = array(
    'dbname' => '',
    'user' => '',
    'password' => '',
    'host' => '',
    'driver' => 'pdo_mysql',
    'driverOptions' => array(
        1002 => 'SET NAMES utf8'
    )
);

/*
 * Db connection data if DEVELOPMENT_ENVIRONMENT is true
 */
$pffConfig['databaseConfigDev'] = array(
    'dbname' => 'test_db',
    'user' => 'test',
    'password' => 'test',
    'host' => 'localhost',
    'port' => '33061',
    'driver' => 'pdo_mysql',
    'driverOptions' => array(
        1002 => 'SET NAMES utf8'
    )
);

///////////////////////////////////////
// Modules
///////////////////////////////////////

/*
 * Modules to be loaded
 */
$pffConfig['modules'] = array(
    'pff2-doctrine',
    'logger',
    'main_layout'

);
