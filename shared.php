<?php
// @codeCoverageIgnoreStart

/**
 * General bootstrap operations for the framework.
 *
 * @author paolo.fagni<at>gmail.com
 */

\pff\Core\ServiceContainer::initPimple();
\pff\Core\ServiceContainer::set()['config'] = function ($c) {
    return new \pff\Config();
};
\pff\Core\ServiceContainer::set()['hookmanager'] = function ($c) {
    return new \pff\Core\HookManager();
};
\pff\Core\ServiceContainer::set()['modulemanager'] = function ($c) {
    return new \pff\Core\ModuleManager();
};
\pff\Core\ServiceContainer::set()['helpermanager'] = function ($c) {
    return new \pff\Core\HelperManager();
};
\pff\Core\ServiceContainer::set()['app'] = function ($c) {
    return new \pff\App();
};
\pff\Core\ServiceContainer::set()['yamlparser'] = function ($c) {
    return new \Symfony\Component\Yaml\Parser();
};



$app = \pff\Core\ServiceContainer::get('app');
$app->setUrl($url);
$app->setErrorReporting();
$app->unregisterGlobals();

\pff\Core\ServiceContainer::get('modulemanager')->initModules();
// @codeCoverageIgnoreStop
