<?php

declare(strict_types=1);

namespace pff\modules;

use pff\Abs\AModule;
use pff\Iface\IBeforeSystemHook;

/**
 * Module to manage sessions
 *
 * @author paolo.fagni<at>gmail.com
 */
class Session extends AModule implements IBeforeSystemHook
{
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
     * Executed before the system startup
     */
    public function doBeforeSystem(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
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

            $strictMode = $config->getConfigData('security_session_strict_mode');
            if (!is_bool($strictMode)) {
                $strictMode = true;
            }

            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', $strictMode ? '1' : '0');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $secure,
                'httponly' => $httpOnly,
                'samesite' => $sameSite,
            ]);

            session_start();
        }
    }
}
