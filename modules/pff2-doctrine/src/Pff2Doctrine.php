<?php
/**
 * User: stonedz
 * Date: 2/8/15
 * Time: 5:06 PM
 */

namespace pff\modules;

use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\Psr6\CacheAdapter;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\ORM\EntityManager;
use pff\Abs\AModule;
use pff\Core\ServiceContainer;
use pff\Iface\IBeforeSystemHook;
use pff\Iface\IConfigurableModule;
use Doctrine\ORM\Configuration;
use pff\Config;
use pff\Exception\PffException;

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

    private function initORM()
    {
        /** @var Config */
        $config_pff = ServiceContainer::get('config');
        if (false === $this->redis || $config_pff->getConfigData('development_environment')) {
            $cache =  new PhpFileCache(ROOT . DS . 'tmp' . DS);
        } else {
            $redis = new \Redis();
            if (!$redis->connect($this->redis_host, $this->redis_port)) {
                throw new PffException("Cannot connect to redis", 500);
            }
            if ($this->redis_password != '') {
                if (!$redis->auth($this->redis_password)) {
                    throw new PffException('Cannot authh to redis', 500);
                }
            }
            $cache = new RedisCache();
            $cache->setRedis($redis);
            $cache->setNamespace($config_pff->getConfigData('app_name'));
        }


        $config = new Configuration();
        if(!$config_pff->getConfigData('development_environment')){
            $config->setMetadataCache(CacheAdapter::wrap($cache));
            $config->setQueryCacheImpl($cache);
            $config->setResultCacheImpl($cache);
        }
        $driverImpl = $config->newDefaultAnnotationDriver(ROOT . DS . 'app' . DS . 'models');
        $config->setMetadataDriverImpl($driverImpl);
        $config->setProxyDir(ROOT . DS . 'app' . DS . 'proxies');
        $config->setProxyNamespace('pff\proxies');

        if (true === $config_pff->getConfigData('development_environment')) {
            $config->setAutoGenerateProxyClasses(true);
            $connectionOptions = $config_pff->getConfigData('databaseConfigDev');
        } else {
            $config->setAutoGenerateProxyClasses(false);
            $connectionOptions = $config_pff->getConfigData('databaseConfig');
        }


        $this->db= EntityManager::create($connectionOptions, $config);

        ServiceContainer::set()['dm'] = $this->db;
        $platform = $this->db->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
    }
}
