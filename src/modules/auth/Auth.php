<?php

namespace pff\modules;

use pff\Abs\AModule;
use pff\Iface\IConfigurableModule;
use pff\modules\Abs\APasswordChecker;
use pff\modules\Utils\Md5PasswordChecker;
use pff\modules\Utils\Sha256PasswordChecker;

/**
 * Module to manage user authentification
 *
 * @author paolo.fagni<at>gmail.com
 */
class Auth extends AModule implements IConfigurableModule
{
    /*
     * The model class name
     *
     * @var string
     */
    private $_modelName;

    /**
     * Name of the attribute used as username
     *
     * @var string
     */
    private $_usernameAttribute;

    /**
     * The method to get the username
     *
     * @var string
     */
    private $_methodGetUser;

    /**
     * The method to get the password
     *
     * @var string
     */
    private $_methodGetPassword;

    /**
     * The password encryption method
     *
     * @var string
     */
    private $_encryptionMethod;

    /**
     * Name of the session variable that will be set to 1 if
     * the user is logged
     *
     * @var string
     */
    private $_sessionVarName;

    /**
     * @var APasswordChecker
     */
    private $_encryptionStrategy;

    /**
     * If true use password salts
     *
     * @var bool
     */
    private $_useSalt;

    /**
     * Method to get the salt
     *
     * @var string
     */
    private $_methodGetSalt;


    /**
     * @param string $confFile Path to configuration file
     */
    public function __construct($confFile = 'auth/module.conf.yaml')
    {
        $this->loadConfig($this->readConfig($confFile));
    }

    public function loadConfig($parsedConfig)
    {
        $this->_modelName         = $parsedConfig['moduleConf']['userModelClass'];
        $this->_usernameAttribute = $parsedConfig['moduleConf']['usernameAttribute'];
        $this->_methodGetPassword = $parsedConfig['moduleConf']['userGetPassword'];
        $this->_encryptionMethod  = $parsedConfig['moduleConf']['passwordType'];
        $this->_sessionVarName    = $parsedConfig['moduleConf']['sessionVarName'];
        $this->_useSalt           = $parsedConfig['moduleConf']['useSalt'];
        $this->_methodGetSalt     = $parsedConfig['moduleConf']['userGetSalt'];

        switch ($this->_encryptionMethod) {
            case 'md5':
            case 'MD5':
                $this->_encryptionStrategy = new Md5PasswordChecker();
                break;
            case 'sha2':
            case 'SHA2':
            case 'sha256':
            case 'SHA256':
                $this->_encryptionStrategy = new Sha256PasswordChecker();
                break;
            default: // If no encrytion is selected choose md5
                $this->_encryptionStrategy = new Md5PasswordChecker();
                break;
        }
    }

    /**
     * Checks if a client is logged-in
     *
     * @return bool
     */
    public function checkAuth()
    {
        if (isset($_SESSION[$this->_sessionVarName]) &&
            $_SESSION[$this->_sessionVarName] == 1
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Login
     *
     * @param string $username
     * @param string $password
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @return bool
     */
    public function login($username, $password, $entityManager)
    {
        $tmp = $entityManager
            ->getRepository('pff\models\\' . $this->_modelName)
            ->findOneBy([$this->_usernameAttribute => $username]);
        if ($tmp) {
            if ($this->_encryptionStrategy->checkPass($password, call_user_func([$tmp, $this->_methodGetPassword]), ($this->_useSalt) ? (call_user_func([$tmp,$this->_methodGetSalt])) : '')) {
                $this->_logUser();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Logout
     *
     * @return bool
     */
    public function logout()
    {
        if (isset($_SESSION[$this->_sessionVarName])) {
            unset($_SESSION[$this->_sessionVarName]);
        }
        return true;
    }

    private function _logUser()
    {
        $_SESSION[$this->_sessionVarName] = 1;
    }

    public function getModelName()
    {
        return $this->_modelName;
    }

    public function setModelName($modelName)
    {
        $this->_modelName = $modelName;
    }

    /**
     * @return string
     */
    public function getUsernameAttribute()
    {
        return $this->_usernameAttribute;
    }

    /**
     * @param string $usernameAttribute
     */
    public function setUsernameAttribute($usernameAttribute)
    {
        $this->_usernameAttribute = $usernameAttribute;
    }
}
