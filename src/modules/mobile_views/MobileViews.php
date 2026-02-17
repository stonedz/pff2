<?php

declare(strict_types=1);

namespace pff\modules;

use pff\Abs\AModule;
use pff\Iface\IBeforeHook;
use pff\Iface\IConfigurableModule;

class MobileViews extends AModule implements IConfigurableModule, IBeforeHook
{
    /**
     * @var string
     */
    private string $_suffix;

    /**
     * Session var name.
     *
     * If $_SESSION[$_sessionName] is set to true it means the user is currently using a mobile device
     *
     * @var string
     */
    private string $_sessionName;

    /**
     * Session var name for auto-mode
     *
     * If $_SESSION[$_sessionAutoName] is set to _false_ it means the default behaviour is to ignore mobile device specific views
     *
     * @var string
     */
    private string $_sessionAutoName;

    /**
     * Session var name to force mobile view
     *
     * @var bool
     */
    private bool $_sessionForceMobile = false;

    /**
     * If false desktop version will be displayed to tablets.
     *
     * @var bool
     */
    private bool $_allowMobileForTablet = false;

    /**
     * @var bool
     */
    private bool $_defaultBehaviour;

    /**
     * @var \Detection\MobileDetect
     */
    private \Detection\MobileDetect $_md;

    /**
     * @var bool
     */
    private bool $_isMobile = false;

    /**
     * @var bool
     */
    private bool $_isTablet = false;

    /**
     * @param string $confFile Path to configuration file
     */
    public function __construct(string $confFile = 'mobile_views/module.conf.yaml')
    {
        $this->loadConfig($this->readConfig($confFile));
    }

    /**
     * @param array $parsedConfig
     */
    public function loadConfig(array $parsedConfig): void
    {
        $this->_suffix = $parsedConfig['moduleConf']['filenameSuffix'];
        $this->_sessionName = $parsedConfig['moduleConf']['sessionVarName'];
        $this->_sessionAutoName = $parsedConfig['moduleConf']['sessionVarAutoName'];
        $this->_defaultBehaviour = $parsedConfig['moduleConf']['showMobileVersion'];
        $this->_sessionForceMobile = $parsedConfig['moduleConf']['showMobileOnly'];

        if (isset($parsedConfig['moduleConf']['allowMobileForTablet'])) {
            $this->_allowMobileForTablet = $parsedConfig['moduleConf']['allowMobileForTablet'];
        }

        $this->_md = new \Detection\MobileDetect();
    }

    /**
     * Executes actions before the Controller
     */
    public function doBefore(): void
    {
        $this->_isMobile = $this->_md->isMobile();
        $this->_isTablet = $this->_md->isTablet();

        if (!isset($_SESSION[$this->_sessionAutoName]) && $this->_isTablet) {
            $_SESSION[$this->_sessionAutoName] = $this->_allowMobileForTablet;
        } elseif (!isset($_SESSION[$this->_sessionAutoName]) || $_SESSION[$this->_sessionAutoName] == '') {
            $_SESSION[$this->_sessionAutoName] = $this->_defaultBehaviour;
        }

        $_SESSION[$this->_sessionName] = $this->_isMobile;
    }

    public function shouldLoadMobileViews(): bool
    {
        if ($_SESSION[$this->_sessionAutoName] && $this->_isMobile) {
            return true;
        } else {
            return false;
        }
    }

    public function isMobile(): bool
    {
        if (
            ($this->_isTablet || $this->_isMobile)
            && $_SESSION[$this->_sessionAutoName]
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets the auto mode on or off,
     *
     * true = ON and false = OFF
     *
     * @param bool $val
     */
    private function setAutoMode(bool $val): void
    {
        $this->_defaultBehaviour = $val;
        $_SESSION[$this->_sessionAutoName] = $this->_defaultBehaviour;
    }

    public function autoModeOn(): void
    {
        $this->setAutoMode(true);
    }

    public function autoModeOff(): void
    {
        $this->setAutoMode(false);
    }

    public function getAutoMode(): mixed
    {
        return $_SESSION[$this->_sessionAutoName];
    }

    // GETTERS & SETTERS

    /**
     * @param boolean $defaultBehaviour
     */
    public function setDefaultBehaviour(bool $defaultBehaviour): void
    {
        $this->_defaultBehaviour = $defaultBehaviour;
    }

    /**
     * @return boolean
     */
    public function getDefaultBehaviour(): bool
    {
        return $this->_defaultBehaviour;
    }

    /**
     * @param string $sessionName
     */
    public function setSessionName(string $sessionName): void
    {
        $this->_sessionName = $sessionName;
    }

    /**
     * @return string
     */
    public function getSessionName(): string
    {
        return $this->_sessionName;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix(string $suffix): void
    {
        $this->_suffix = $suffix;
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->_suffix;
    }

    /**
     * @param boolean $sessionForceMobile
     */
    public function setMobileViewOnly(bool $sessionForceMobile): void
    {
        $this->_sessionForceMobile = $sessionForceMobile;
    }

    /**
     * @return boolean
     */
    public function getMobileViewOnly(): bool
    {
        return $this->_sessionForceMobile;
    }
}
