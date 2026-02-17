<?php

declare(strict_types=1);

namespace pff\modules\Exception;

use pff\Exception\PffException;

/**
 * Exception thrown on CSRF token validation failures.
 */
class CsrfException extends PffException
{
}
