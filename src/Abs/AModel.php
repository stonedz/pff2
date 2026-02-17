<?php

declare(strict_types=1);

namespace pff\Abs;

/**
 * Every model must implement this abstract class
 *
 * @author paolo.fagni<at>gmail.com
 */
abstract class AModel
{
    protected ?\pff\App $_app = null;

    public function setApp(\pff\App $app): void
    {
        $this->_app = $app;
    }
}
