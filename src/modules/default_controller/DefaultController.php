<?php

declare(strict_types=1);

namespace pff\modules;

use pff\Abs\AModule;
use pff\Iface\IBeforeSystemHook;
use pff\Iface\IConfigurableModule;

/**
 * This module loads a specific controller if the user requested controller is not valid
 *
 * @author paolo.fagni<at>gmail.com
 */
class DefaultController extends AModule implements IConfigurableModule, IBeforeSystemHook
{
    /**
     * @var string
     */
    private string $_defaultController;

    public function __construct(string $confFile = 'default_controller/module.conf.yaml')
    {
        $this->loadConfig($this->readConfig($confFile));
    }

    /**
     * Parse the configuration file
     *
     * @param array $parsedConfig
     */
    public function loadConfig(array $parsedConfig): void
    {
        $this->_defaultController = $parsedConfig['moduleConf']['defaultController'];
    }

    /**
     * Executed before the system startup
     */
    public function doBeforeSystem(): void
    {
        $tmpUrl = $this->_app->getUrl();
        $tmpUrl = explode('/', $tmpUrl);
        if (file_exists(ROOT . DS . 'app' . DS . 'controllers' . DS . ucfirst($tmpUrl[0]) . '_Controller.php')) {
            return;
        } elseif (file_exists(ROOT . DS . 'app' . DS . 'pages' . DS . $tmpUrl[0]) && $tmpUrl[0] != '') {
            return;
        }
        $this->_app->setUrl($this->_defaultController . '/' . implode('/', $tmpUrl));
    }
}
