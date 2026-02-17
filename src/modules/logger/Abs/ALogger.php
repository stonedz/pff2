<?php

declare(strict_types=1);

namespace pff\modules\Abs;

/**
 * Abstract class that must be exteded by any logger.
 *
 * @author paolo.fagni<at>gmail.com
 */
abstract class ALogger
{
    // Log levels
    public const LVL_NORM = 0;
    public const LVL_ERR = 1;
    public const LVL_FATAL = 2;

    /**
     * Log level names
     *
     * @var string[]
     */
    protected array $_levelNames = [];

    public function __construct(/**
       * Debug mode
       */
        protected bool $_debugActive = false
    ) {
        $this->_levelNames[self::LVL_NORM] = 'NORMAL';
        $this->_levelNames[self::LVL_ERR] = 'ERROR';
        $this->_levelNames[self::LVL_FATAL] = 'FATAL';
    }

    /**
     * Logs a message
     *
     * @param string $message Message to log
     * @param int $level Level to log the message
     */
    abstract public function logMessage(string $message, int $level = 0): void;
}
