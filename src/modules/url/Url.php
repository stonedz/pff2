<?php

declare(strict_types=1);

namespace pff\modules;

use pff\Abs\AModule;

/**
 * User: alessandro
 * Date: 05/10/12
 * Time: 11.39
 */
class Url extends AModule
{
    public function clear_string(string $str, array $replace = [], string $delimiter = '-'): string
    {
        if (!empty($replace)) {
            $str = str_replace((array) $replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim((string) $clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

    public function make_url(int|string $id, string $text): string
    {
        $result = $id . '-' . $this->clear_string($text);
        return $result;
    }

    public function get_id(string $text): string
    {
        $result = explode("-", $text);
        return $result[0];
    }
}
