<?php

declare(strict_types=1);

namespace pff\modules\Exception;

use pff\Exception\PffException;

/**
 * Generic logger exception
 *
 * @author paolo.fagni<at>gmail.com
 */
class LoggerException extends PffException
{
    /**
     * contains the backtrack of all the callers.
     *
     * @var array
     */
    public array $backtrace;

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->backtrace = debug_backtrace();
    }
}
