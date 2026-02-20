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
