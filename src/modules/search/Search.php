<?php

namespace pff\modules;

use pff\Abs\AModule;

/**
 * Helper module to search into a database
 *
 * @author marco.sangiorgi<at>neropaco.net
 */


class Search extends AModule
{
    public function __construct()
    {
    }

    /**
     * @param $modelnames
     * @return Searcher
     */
    public function createSearcher($modelnames)
    {
        $params = [];
        if (!is_array($modelnames)) {
            array_push($params, $modelnames);
        } else {
            $params = $modelnames;
        }
        $factory = new Searcher($params);
        return $factory;
    }
}
