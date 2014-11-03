<?php
/**
 * @author paolo.fagni<at>gmail.com
 */
if (php_sapi_name() != "cli") {
    throw new \Exception('can\'t do that');
}
require 'vendor/autoload.php';
require 'app/config/config.user.php';

// Define application environment
define('APPLICATION_ENV', "development");

// configuration (2)
$config = new Doctrine\ORM\Configuration();

// Proxies (3)
$config->setProxyDir('app/proxies');
$config->setProxyNamespace('pff\proxies');

$config->setAutoGenerateProxyClasses((APPLICATION_ENV == "development"));

// Driver (4)
$driverImpl = $config->newDefaultAnnotationDriver(array('app/models'));
$config->setMetadataDriverImpl($driverImpl);

// Caching Configuration (5)
if (APPLICATION_ENV == "development") {

    $cache = new \Doctrine\Common\Cache\ArrayCache();

} else {

    $cache = new \Doctrine\Common\Cache\ApcCache();
}

$config->setMetadataCacheImpl($cache);
$config->setQueryCacheImpl($cache);

if (true === $pffConfig['development_environment']) {
    $connectionOptions = $pffConfig['databaseConfigDev'];
} else {
    $connectionOptions = $pffConfig['databaseConfig'];
}


$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));

$platform = $em->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');

foreach ($GLOBALS as $helperSetCandidate) {
    if ($helperSetCandidate instanceof \Symfony\Component\Console\Helper\HelperSet) {
        $helperSet = $helperSetCandidate;
        break;
    }
}

$helperSet = ($helperSet) ?: new \Symfony\Component\Console\Helper\HelperSet();

\Doctrine\ORM\Tools\Console\ConsoleRunner::run($helperSet);
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($em);
