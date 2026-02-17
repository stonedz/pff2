<?php

declare(strict_types=1);

namespace pff;

use pff\Exception\ConfigException;

/**
 * Manages pff configuration.
 *
 * Main configuration file is in ROOT/app/config/config.user.php
 * Additional configuration files may be added from modules.
 *
 * @author paolo.fagni<at>gmail.com
 */
class Config
{
    /**
     * @var array<string, mixed> Contains app configurations
     */
    private array $_config;

    public function __construct(string $configFile = 'config.user.php', string $configPath = 'app/config')
    {
        $this->_config = [];
        $this->loadConfig($configFile, $configPath); // Load main config file
    }

    /**
     * Load a configuration file
     *
     * @param string $configFile Name of the file
     * @param string $configPath Path of the config file
     * @throws ConfigException
     * @return void
     */
    public function loadConfig(string $configFile = 'config.user.php', string $configPath = 'app/config'): void
    {
        $completePath = ROOT . DS . $configPath . DS . $configFile;

        if (!file_exists($completePath)) {
            throw new ConfigException("Specified config file does not exist: " . $completePath);
        }

        include($completePath);

        if (isset($pffConfig) && is_array($pffConfig)) {
            $this->_config = array_merge($this->_config, $pffConfig);
        } else {
            throw new ConfigException("Failed to load configuration file!
                                            The file seems to be corrupted: " . $completePath);
        }
    }

    /**
     * Gets configuration
     *
     * @param null|string $data Wanted config param
     * @return array|mixed
     */
    public function getConfigData(?string $data = null): mixed
    {
        if ($data !== null && isset($this->_config[$data])) {
            return $this->_config[$data];
        } elseif ($data === null) {
            return $this->_config;
        } else {
            return false;
        }
    }

    /**
     * Gets configuration, deprecated!!
     *
     * @param null|string $data Wanted config param
     * @throws ConfigException
     * @return array|mixed
     *
     * @deprecated Use getConfigData instead
     * @see getConfigData
     */
    public function getConfig(?string $data = null): mixed
    {
        return $this->getConfigData($data);
    }

    /**
     * Sets a configuration,if the configuration already exists it OVERWRITES the old one.
     *
     * @param string $data
     * @throws ConfigException
     */
    public function setConfig(string $data, mixed $value): void
    {
        if (is_string($data)) {
            $this->_config[$data] = $value;
        } else {
            throw new ConfigException("Error while setting a config value");
        }
    }
}
