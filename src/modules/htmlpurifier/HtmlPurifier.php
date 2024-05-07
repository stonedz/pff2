<?php

namespace pff\modules;

use pff\Abs\AModule;

/**
 *
 * @author paolo.fagni<at>gmail.com
 */
class HtmlPurifier extends AModule
{
    public function __construct()
    {
        if (!defined('HTMLPURIFIER_PREFIX')) {
            define('HTMLPURIFIER_PREFIX', realpath(__DIR__ . '/../../vendor/ezyang/htmlpurifier/library'));
        }
    }

    /**
     * Purifies an HTML string with htmlpurifier
     *
     * @param string $output
     * @return string
     */
    public function purify($output)
    {
        /** @var $purifierConfig \HTMLPurifier_Config */
        $purifierConfig = \HTMLPurifier_Config::createDefault();
        $purifierConfig->set('Core.Encoding', 'UTF-8');
        $purifierConfig->set('Attr.EnableID', true);
        $purifierConfig->set('HTML.TidyLevel', 'medium');
        $purifier = new \HTMLPurifier($purifierConfig);
        $output   = $purifier->purify($output);

        return $output;
    }
}
