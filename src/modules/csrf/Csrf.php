<?php

namespace pff\modules;

use pff\Abs\AModule;
use pff\Iface\IBeforeHook;
use pff\Iface\IConfigurableModule;
use pff\modules\Exception\CsrfException;

/**
 * CSRF protection module.
 *
 * Generates and validates anti-CSRF tokens stored in the session.
 * When enabled, automatically validates tokens on POST/PUT/PATCH/DELETE requests.
 *
 * Usage in forms:
 *   <?= $csrf->tokenField('my_form') ?>
 *
 * Usage in controllers:
 *   $csrf = ModuleManager::loadModule('csrf');
 *   $csrf->validateToken('my_form', $_POST['_csrf_token']); // throws CsrfException on failure
 *
 * @author paolo.fagni<at>gmail.com
 */
class Csrf extends AModule implements IBeforeHook, IConfigurableModule
{
    /**
     * Whether automatic validation is enabled for state-changing requests.
     *
     * @var bool
     */
    private bool $_autoValidate = true;

    /**
     * Token lifetime in seconds (default: 3600 = 1 hour).
     *
     * @var int
     */
    private int $_tokenLifetime = 3600;

    /**
     * Name of the POST/header field carrying the token.
     *
     * @var string
     */
    private string $_fieldName = '_csrf_token';

    /**
     * Name of the HTTP header carrying the token (for AJAX).
     *
     * @var string
     */
    private string $_headerName = 'X-CSRF-Token';

    /**
     * Routes excluded from CSRF validation (e.g. webhook endpoints).
     *
     * @var string[]
     */
    private array $_excludedRoutes = [];

    /**
     * HTTP methods that require CSRF validation.
     *
     * @var string[]
     */
    private array $_protectedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * @param string $confFile
     */
    public function __construct(string $confFile = 'csrf/module.conf.yaml')
    {
        try {
            $this->loadConfig($this->readConfig($confFile));
        } catch (\pff\Exception\ModuleException $e) {
            // No config file found — use defaults
        }
    }

    /**
     * @param array $parsedConfig
     */
    public function loadConfig($parsedConfig): void
    {
        if (isset($parsedConfig['moduleConf'])) {
            $conf = $parsedConfig['moduleConf'];
            $this->_autoValidate = (bool) ($conf['autoValidate'] ?? $this->_autoValidate);
            $this->_tokenLifetime = (int) ($conf['tokenLifetime'] ?? $this->_tokenLifetime);
            $this->_fieldName = (string) ($conf['fieldName'] ?? $this->_fieldName);
            $this->_headerName = (string) ($conf['headerName'] ?? $this->_headerName);
            $this->_excludedRoutes = (array) ($conf['excludedRoutes'] ?? $this->_excludedRoutes);
        }
    }

    /**
     * Hook: automatically validates CSRF tokens on state-changing requests.
     */
    public function doBefore(): void
    {
        if (!$this->_autoValidate) {
            return;
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, $this->_protectedMethods, true)) {
            return;
        }

        // Check excluded routes
        $currentUrl = $_GET['url'] ?? '';
        foreach ($this->_excludedRoutes as $route) {
            if ($currentUrl === $route || str_starts_with($currentUrl, $route . '/')) {
                return;
            }
        }

        // Look for token in POST body or HTTP header
        $token = $_POST[$this->_fieldName]
            ?? $_SERVER['HTTP_' . str_replace('-', '_', strtoupper($this->_headerName))]
            ?? null;

        if ($token === null) {
            throw new CsrfException('CSRF token missing.', 403);
        }

        // Validate against any stored token
        if (!$this->isValidToken($token)) {
            throw new CsrfException('CSRF token validation failed.', 403);
        }
    }

    /**
     * Generates a new CSRF token for a given action/form.
     *
     * @param string $action Identifier for the form/action
     * @return string The generated token
     */
    public function generateToken(string $action = 'default'): string
    {
        $this->ensureSession();

        $token = bin2hex(random_bytes(32));

        $_SESSION['_csrf_tokens'][$action] = [
            'token' => $token,
            'time' => time(),
        ];

        return $token;
    }

    /**
     * Validates a CSRF token for a given action/form.
     *
     * @param string $action Identifier for the form/action
     * @param string $token The token to validate
     * @return bool
     * @throws CsrfException If validation fails
     */
    public function validateToken(string $action, string $token): bool
    {
        $this->ensureSession();

        if (!isset($_SESSION['_csrf_tokens'][$action])) {
            throw new CsrfException('CSRF token not found for action: ' . $action, 403);
        }

        $stored = $_SESSION['_csrf_tokens'][$action];

        // Check expiration
        if ((time() - $stored['time']) > $this->_tokenLifetime) {
            unset($_SESSION['_csrf_tokens'][$action]);
            throw new CsrfException('CSRF token expired.', 403);
        }

        // Timing-safe comparison
        if (!hash_equals($stored['token'], $token)) {
            throw new CsrfException('CSRF token mismatch.', 403);
        }

        // Token is single-use: remove after successful validation
        unset($_SESSION['_csrf_tokens'][$action]);

        return true;
    }

    /**
     * Returns an HTML hidden input element with a fresh CSRF token.
     *
     * Usage in templates:
     *   <?= $csrf->tokenField('my_form') ?>
     *
     * @param string $action Identifier for the form/action
     * @return string HTML hidden input
     */
    public function tokenField(string $action = 'default'): string
    {
        $token = $this->generateToken($action);
        $escapedName = htmlspecialchars($this->_fieldName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $escapedToken = htmlspecialchars($token, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return '<input type="hidden" name="' . $escapedName . '" value="' . $escapedToken . '">';
    }

    /**
     * Returns the current token for an action (without generating a new one).
     *
     * Useful for AJAX requests where the token is sent via header.
     *
     * @param string $action
     * @return string|null
     */
    public function getToken(string $action = 'default'): ?string
    {
        $this->ensureSession();

        return $_SESSION['_csrf_tokens'][$action]['token'] ?? null;
    }

    /**
     * Get the configured field name.
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->_fieldName;
    }

    /**
     * Get the configured header name.
     *
     * @return string
     */
    public function getHeaderName(): string
    {
        return $this->_headerName;
    }

    /**
     * Checks if a token is valid against any stored action token.
     * Used by the automatic doBefore() hook.
     *
     * @param string $token
     * @return bool
     */
    private function isValidToken(string $token): bool
    {
        $this->ensureSession();

        if (!isset($_SESSION['_csrf_tokens']) || !is_array($_SESSION['_csrf_tokens'])) {
            return false;
        }

        foreach ($_SESSION['_csrf_tokens'] as $action => $stored) {
            // Skip expired tokens
            if ((time() - $stored['time']) > $this->_tokenLifetime) {
                unset($_SESSION['_csrf_tokens'][$action]);
                continue;
            }

            if (hash_equals($stored['token'], $token)) {
                // Single-use: consume the token
                unset($_SESSION['_csrf_tokens'][$action]);
                return true;
            }
        }

        return false;
    }

    /**
     * Ensures a session is active.
     */
    private function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['_csrf_tokens'])) {
            $_SESSION['_csrf_tokens'] = [];
        }
    }
}
