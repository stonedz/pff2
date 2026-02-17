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

/*
 * Show exception details (message and stack trace) in error views.
 * If omitted, it follows development_environment. Keep false in production.
 */
$pffConfig['show_exception_details'] = true;

/*
 * Cookie and session security defaults.
 * security_cookie_secure: true|false|null(auto-detect HTTPS/proxy headers)
 */
$pffConfig['security_cookie_httponly'] = true;
$pffConfig['security_cookie_samesite'] = 'Lax';
$pffConfig['security_cookie_secure'] = null;
$pffConfig['security_session_strict_mode'] = true;

/*
 * Security headers sent with every HTML response.
 * Set a header to null to suppress it. Override per-controller via $this->getOutput()->setHeader().
 * See docs/security.md for details.
 */
$pffConfig['security_headers'] = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    // 'Content-Security-Policy' => "default-src 'self'",
];

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
    'host' => 'mysql',
    'port' => '3306',
    'driver' => 'pdo_mysql',
    'driverOptions' => array(
        1002 => 'SET NAMES utf8'
    )
);

/*
 * Db connection data to use with the CLI ***OUTSIDE*** the container is true
 */
$pffConfig['databaseConfigCli'] = array(
    'dbname' => 'test_db',
    'user' => 'test',
    'password' => 'test',
    'host' => '127.0.01',
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
