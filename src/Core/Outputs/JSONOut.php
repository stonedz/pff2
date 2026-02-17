<?php

declare(strict_types=1);

/**
 * User: paolo.fagni@gmail.com
 * Date: 07/11/14
 * Time: 11.33
 */

namespace pff\Core\Outputs;

use pff\Iface\IOutputs;

class JSONOut implements IOutputs
{
    public function outputHeader(): void
    {
        header('Content-type: application/json');
    }
}
