<?php

namespace pff\Core;
use pff\Abs\AView;

/**
 * Smarty view adapter
 *
 * @author paolo.fagni<at>gmail.com
 */
class ViewSmarty extends AView {

    /**
     * @var \Smarty
     */
    protected $_smarty;

    public function __construct($templateName) {
        $this->_smarty               = new \Smarty(); // The smarty instance should be accessible before
        $smartyDir                   = ROOT . DS . 'app' . DS . 'views' . DS . 'smarty' . DS;
        $this->_smarty->template_dir = $smartyDir. 'templates' . DS;
        $this->_smarty->compile_dir  = $smartyDir . 'compiled_templates' . DS;
        $this->_smarty->config_dir   = $smartyDir . 'configs' . DS;
        $this->_smarty->cache_dir    = $smartyDir . 'cache' . DS;
        $templatePath = ROOT . DS . 'app' . DS . 'views' . DS . 'smarty' . DS . 'templates' . DS . $templateName;
        if (!file_exists($templatePath)) {
            throw new \pff\ViewException('Template file ' . $templatePath . ' does not exist');
        }
        parent::__construct($templateName);
        $this->_smarty->registerPlugin('function', 'renderAction', array($this, 'smarty_plugin_renderAction'));
    }

    public function smarty_plugin_renderAction($params, $smarty) {
        if (!isset($params['params'])) {
            $params['params'] = array();
        }

        if (!isset($params['action'])) {
            $params['action'] = 'index';
        }
        $this->renderAction($params['controller'], ($params['action']), $params['params']);
    }

    public function set($name, $value) {
        $this->_smarty->assign($name, $value);
    }

    public function render() {
        $this->_smarty->display($this->_templateFile);
    }

    public function renderHtml() {
        return $this->_smarty->fetch($this->_templateFile,null,null,null,false);
    }
}
