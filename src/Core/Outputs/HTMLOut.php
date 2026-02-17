<?php
/**
 * User: paolo.fagni@gmail.com
 * Date: 07/11/14
 * Time: 11.25
 */

namespace pff\Core\Outputs;

use pff\Core\ServiceContainer;
use pff\Iface\IOutputs;

class HTMLOut implements IOutputs
{
    /**
     * Default security headers.
     *
     * @var array<string, string>
     */
    private array $_defaultHeaders = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ];

    /**
     * Per-instance header overrides.
     *
     * @var array<string, string|null>
     */
    private array $_overrides = [];

    /**
     * Sends HTML content-type and security headers.
     *
     * Headers can be configured via $pffConfig['security_headers'] (associative array).
     * Set a header value to null to suppress it entirely.
     * Override per-controller/request via setHeader()/removeHeader().
     */
    public function outputHeader(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Content-Type: text/html; charset=UTF-8');

        $headers = $this->_defaultHeaders;

        // Merge config-level overrides
        try {
            $config = ServiceContainer::get('config');
            $configHeaders = $config->getConfigData('security_headers');
            if (is_array($configHeaders)) {
                $headers = array_merge($headers, $configHeaders);
            }
        } catch (\Throwable $e) {
            // Config not available yet (e.g. early error) — use defaults
        }

        // Merge per-instance overrides
        $headers = array_merge($headers, $this->_overrides);

        foreach ($headers as $name => $value) {
            if ($value === null) {
                // Null means "suppress this header"
                continue;
            }
            header($name . ': ' . $value);
        }
    }

    /**
     * Set or override a specific security header.
     *
     * @param string $name Header name (e.g. 'X-Frame-Options')
     * @param string $value Header value (e.g. 'SAMEORIGIN')
     */
    public function setHeader(string $name, string $value): void
    {
        $this->_overrides[$name] = $value;
    }

    /**
     * Remove (suppress) a specific security header.
     *
     * @param string $name Header name to suppress
     */
    public function removeHeader(string $name): void
    {
        $this->_overrides[$name] = null;
    }
}
