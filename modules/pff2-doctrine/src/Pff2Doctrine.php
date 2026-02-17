<?php

/**
 * User: stonedz
 * Date: 2/8/15
 * Time: 5:06 PM
 */

namespace pff\modules;

use pff\Abs\AModule;
use pff\Core\ServiceContainer;
use pff\Iface\IBeforeSystemHook;
use pff\Iface\IConfigurableModule;
use Doctrine\ORM\Configuration;
use pff\Config;
use pff\Exception\PffException;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\ORMSetup;

class Pff2Doctrine extends AModule implements IConfigurableModule, IBeforeSystemHook
{
    /**
     * @var EntityManager
     */
    private $db;
    private $redis;
    private $redis_port;
    private $redis_host;
    private $redis_password;

    public function __construct($confFile = 'pff2-doctrine/module.conf.local.yaml')
    {
        $this->loadConfig($confFile);
    }

    /**
     * @param array $parsedConfig
     * @return mixed
     */
    public function loadConfig($parsedConfig)
    {
        $conf = $this->readConfig($parsedConfig);
        $this->redis = $conf['moduleConf']['redis'];
        $this->redis_port = $conf['moduleConf']['redis_port'];
        $this->redis_host = $conf['moduleConf']['redis_host'];
        $this->redis_password = $conf['moduleConf']['redis_password'];
    }

    /**
     * Executed before the system startup
     *
     * @return mixed
     */
    public function doBeforeSystem()
    {
        $this->initORM();
    }

    private function initORM(): void
    {
        $config_pff = ServiceContainer::get('config');
        $paths = [ROOT . DS . 'app' . DS . 'models'];

        if (true === $config_pff->getConfigData('development_environment')) {
            $dbParams = $config_pff->getConfigData('databaseConfigDev');
            $isDevMode = true;
        } else {
            $dbParams = $config_pff->getConfigData('databaseConfig');
            $isDevMode = false;
        }

        $config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);

        $config->setProxyDir(ROOT . DS . 'app' . DS . 'proxies');
        $config->setProxyNamespace('pff\proxies');

        $driverImpl = new AttributeDriver([ROOT . DS . 'app' . DS . 'models'], true);
        $config->setMetadataDriverImpl($driverImpl);

        $connection = DriverManager::getConnection($dbParams, $config);
        $this->db = new EntityManager($connection, $config);

        ServiceContainer::set()['dm'] = $this->db;
    }
}
