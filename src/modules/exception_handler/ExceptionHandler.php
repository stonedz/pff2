<?php

declare(strict_types=1);

namespace pff\modules;

use pff\Abs\AModule;
use pff\Exception\PffException;
use pff\Factory\FView;
use pff\Iface\IBeforeSystemHook;

/**
 * Manages uncaught exceptions
 *
 * @author paolo.fagni<at>gmail.com
 */
class ExceptionHandler extends AModule implements IBeforeSystemHook
{
    /**
     * Executed before the system startup
     */
    public function doBeforeSystem(): void
    {
        set_exception_handler($this->manageExceptions(...));
    }

    /**
     * @param \Exception $exception
     */
    public function manageExceptions(\Throwable $exception): void
    {
        $code = (int) $exception->getCode();
        $httpCode = ($code >= 400 && $code <= 599) ? $code : 500;
        http_response_code($httpCode);

        $isDevelopment = ($this->getConfig()->getConfigData('development_environment') === true);
        $showExceptionDetails = $this->getConfig()->getConfigData('show_exception_details');
        if (!is_bool($showExceptionDetails)) {
            $showExceptionDetails = $isDevelopment;
        }

        if (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . 'smarty' . DS . 'templates' . DS . $httpCode . '_View.tpl')) {
            $viewPath = $httpCode . '_View.tpl';
        } elseif (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . $httpCode . '_View.php')) {
            $viewPath = $httpCode . '_View.php';
        } elseif (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . 'smarty' . DS . 'templates' . DS . 'defaultError_View.tpl')) {
            $viewPath = 'defaultError_View.tpl';
        } elseif (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . 'defaultError_View.php')) {
            $viewPath = 'defaultError_View.php';
        } elseif (file_exists(ROOT_LIB . DS . 'src' . DS . 'modules' . DS . 'exception_handler' . DS . 'views' . DS . 'default' . $httpCode . '_View.php')) {
            $viewPath = ROOT_LIB . DS . 'src' . DS . 'modules' . DS . 'exception_handler' . DS . 'views' . DS . 'default' . $httpCode . '_View.php';
        } else {
            $viewPath = ROOT_LIB . DS . 'src' . DS . 'modules' . DS . 'exception_handler' . DS . 'views' . DS . 'defaultError_View.php';
        }
        $view = FView::create($viewPath, $this->getApp());
        $view->set('message', $showExceptionDetails ? $exception->getMessage() : 'An unexpected error occurred.');
        $view->set('code', $httpCode);
        $view->set('trace', $showExceptionDetails ? $exception->getTrace() : []);

        if ($showExceptionDetails && is_a($exception, '\pff\Exception\PffException')) {
            /** @var PffException $exception */
            $exceptionParams = $exception->getViewParams();
            if ($exceptionParams !== null && is_array($exceptionParams)) {
                $view->set('exceptionParams', $exceptionParams);
                foreach ($exceptionParams as $k => $v) {
                    $view->set($k, $v);
                }
            }
        }
        if (isset($this->_controller)) {
            $this->_controller->resetViews();
        }
        $view->render();
    }
}
