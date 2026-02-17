<?php

declare(strict_types=1);

namespace pff\modules;

use pff\Abs\AModule;

/**
 * Helper module to manage cookies
 *
 * @author paolo.fagni<at>gmail.com
 */
class Cookies extends AModule
{
    /**
     * If true use encryped cookies
     *
     * @var bool
     */
    private bool $_useEncryption;

    /**
     * Returns true if current request is HTTPS (including reverse proxies).
     *
     * @return bool
     */
    private function isSecureRequest(): bool
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== '') {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getCookieOptions(?int $expire = null): array
    {
        $config = $this->getConfig();

        $httpOnly = $config->getConfigData('security_cookie_httponly');
        if (!is_bool($httpOnly)) {
            $httpOnly = true;
        }

        $sameSite = $config->getConfigData('security_cookie_samesite');
        if (!is_string($sameSite) || !in_array(strtolower($sameSite), ['lax', 'strict', 'none'], true)) {
            $sameSite = 'Lax';
        } else {
            $sameSite = ucfirst(strtolower($sameSite));
        }

        $secureCookieConfig = $config->getConfigData('security_cookie_secure');
        if (is_bool($secureCookieConfig)) {
            $secure = $secureCookieConfig;
        } else {
            $secure = $this->isSecureRequest();
        }

        if ($sameSite === 'None') {
            $secure = true;
        }

        return [
            'expires' => $expire,
            'path' => '/',
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ];
    }

    public function __construct(string $confFile = 'cookies/module.conf.yaml')
    {
        $this->loadConfig($this->readConfig($confFile));
    }

    /**
     * Parse the configuration file
     *
     * @param array $parsedConfig
     */
    private function loadConfig(array $parsedConfig): void
    {
        $this->_useEncryption = $parsedConfig['moduleConf']['useEncryption'];
    }

    /**
     * Sets a cookie in the user's browser
     *
     * @param string $cookieName
     * @param string|null $value The value to store in the cookie
     * @param int|null $expire how many HOURS the cookie will be valid (set to 0 for session time)
     * @return bool
     */
    public function setCookie(string $cookieName, ?string $value = null, ?int $expire = null): bool
    {
        if ($expire !== null) {
            $expire = time() + (60 * 60 * $expire);
        }

        if (setcookie($cookieName, $this->encodeCookie($value), ['expires' => $this->getCookieOptions($expire)])) {
            return true;
        } else {
            return false;
        }
    }

    private function encodeCookie(?string $value): string
    {
        if ($this->_useEncryption) {
            $encryptionModule = $this->getRequiredModules('encryption');
            if ($encryptionModule !== null && method_exists($encryptionModule, 'encrypt')) {
                return $encryptionModule->encrypt($value);
            }
            return $value;
        } else {
            return $value;
        }
    }

    private function decodeCookie(string $value): string
    {
        if ($this->_useEncryption) {
            $encryptionModule = $this->getRequiredModules('encryption');
            if ($encryptionModule !== null && method_exists($encryptionModule, 'decrypt')) {
                return $encryptionModule->decrypt($value);
            }
            return $value;
        } else {
            return $value;
        }
    }

    /**
     * Check if a cookie is set and returns its content
     *
     * @param string $cookieName
     * @return string|false
     */
    public function getCookie(string $cookieName): string|false
    {
        if (isset($_COOKIE[$cookieName])) {
            return $this->decodeCookie($_COOKIE[$cookieName]);
        } else {
            return false;
        }
    }

    /**
     * Deletes a cookie
     *
     * @param string $cookieName Name of the cookie to delete
     * @return bool
     */
    public function deleteCookie(string $cookieName): bool
    {
        if (isset($_COOKIE[$cookieName])) {
            if (setcookie($cookieName, '', ['expires' => $this->getCookieOptions(time() - 6000)])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
