<?php

namespace pff\modules;
use pff\Abstact\AModule;
use pff\Iface\IBeforeSystemHook;
use pff\Exception\PffException;

/**
 * Manages uncaught exceptions
 *
 * @author paolo.fagni<at>gmail.com
 */
class ExceptionHandler extends AModule implements IBeforeSystemHook {

    /**
     * Executed before the system startup
     *
     * @return mixed
     */
    public function doBeforeSystem() {
        set_exception_handler(array($this, 'manageExceptions'));
    }

    /**
     * @param \Exception $exception
     * @todo refactor
     */
    public function manageExceptions(\Exception $exception) {
        $code = (int)$exception->getCode();
        header(' ', true, $code);

        if(file_exists(ROOT . DS . 'app' . DS . 'views' . DS . 'smarty' . DS . 'templates' . DS .$code . '_View.tpl')){
            $viewPath = $code . '_View.tpl';
        }
        elseif (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . $code . '_View.php')) {
            $viewPath = $code . '_View.php';
        }
        elseif(file_exists(ROOT . DS . 'app' . DS . 'views' . DS . 'smarty' . DS . 'templates' . DS .'defaultError_View.tpl')){
            $viewPath = 'defaultError_View.tpl';
        }
        elseif(file_exists(ROOT . DS . 'app' . DS . 'views' . DS . 'defaultError_View.php')) {
            $viewPath = 'defaultError_View.php';
        }
        elseif (file_exists(ROOT_LIB . DS . 'src' . DS . 'modules' . DS . 'exception_handler' . DS . 'views' . DS . 'default' . $code . '_View.php')) {
            $viewPath = ROOT_LIB . DS . 'src' . DS . 'modules' . DS . 'exception_handler' . DS . 'views' . DS . 'default' . $code . '_View.php';
        }
        else {
            $viewPath = ROOT_LIB . DS . 'src' . DS . 'modules' . DS . 'exception_handler' . DS . 'views' . DS . 'defaultError_View.php';
        }
        //die($viewPath);
        $view = \pff\FView::create($viewPath, $this->getApp());
        $view->set('message', $exception->getMessage());
        $view->set('code', $exception->getCode());
        $view->set('trace', $exception->getTrace());

        if(is_a($exception, '\pff\Exception\PffException')) {
            /** @var PffException $exception */
            $exceptionParams = $exception->getViewParams();
            if ($exceptionParams !== null && is_array($exceptionParams)) {
                $view->set('exceptionParams', $exceptionParams);
                foreach ($exceptionParams as $k => $v) {
                    $view->set($k, $v);
                }
            }
        }
        if(isset($this->_controller)) {
            $this->_controller->resetViews();
        }
        $view->render();
    }
}
