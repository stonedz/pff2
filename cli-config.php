<?php

/**
 * @author paolo.fagni<at>gmail.com
 */

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;

if (php_sapi_name() != "cli") {
    throw new \Exception('can\'t do that');
}
require 'vendor/autoload.php';

// Prepare PFF2 container
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('ROOT')) {
    define('ROOT', __DIR__);
}
// Needed by pff\Config to find files if using ROOT_LIB
if (!defined('ROOT_LIB')) {
    define('ROOT_LIB', ROOT . DS . 'vendor' . DS . 'stonedz' . DS . 'pff2');
}

\pff\Core\ServiceContainer::initPimple();
\pff\Core\ServiceContainer::set()['config'] = function ($c) {
    return new \pff\Config();
};
\pff\Core\ServiceContainer::set()['hookmanager'] = function ($c) {
    return new \pff\Core\HookManager();
};
\pff\Core\ServiceContainer::set()['app'] = function ($c) {
    return new \pff\App();
};
\pff\Core\ServiceContainer::set()['modulemanager'] = function ($c) {
    return new \pff\Core\ModuleManager();
};
\pff\Core\ServiceContainer::set()['helpermanager'] = function ($c) {
    return new \pff\Core\HelperManager();
};
\pff\Core\ServiceContainer::set()['yamlparser'] = function ($c) {
    return new \Symfony\Component\Yaml\Parser();
};

require 'app/config/config.user.php';


$paths = ['app/models'];

/** @var array $pffConfig */
if ($pffConfig['development_environment'] === true) {
    $dbParams = $pffConfig['databaseConfigCli'];
} else {
    $dbParams = $pffConfig['databaseConfig'];
}

$isDevMode = false;

$config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);

$config->setProxyDir('app/proxies');
$config->setProxyNamespace('pff\proxies');

$driverImpl = new AttributeDriver($paths);
$config->setMetadataDriverImpl($driverImpl);

$connection = DriverManager::getConnection($dbParams, $config);
$db = new EntityManager($connection, $config);

$commands = [
    // If you want to add your own custom console commands,
    // you can do so here.
];

ConsoleRunner::run(
    new SingleManagerProvider($db),
    $commands
);