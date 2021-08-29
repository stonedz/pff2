<?php

namespace pff\Core;

use pff\Abs\AView;

/**
 * Layout system with Smarty templates
 *
 * A layout is a specific type of View: it represents a common layout with most
 * of its elements fixed and a few placeholders to render custom views.
 *
 * @author paolo.fagni<at>gmail.com
 */
class LayoutSmarty extends ViewSmarty
{
    public function __construct($tempalteName, \pff\App $app)
    {
        parent::__construct($tempalteName, $app);
        $this->_smarty->registerPlugin('function', 'content', [$this, 'smarty_plugin_contentPlaceholder']);
    }

    /**
     * @var AView[]
     */
    private $_contentView;

    /**
     * Adds an Aview to the layout queue
     *
     * @param AView $view
     */
    public function addContent(AView $view)
    {
        $this->_contentView[] = $view;
    }

    public function smarty_plugin_contentPlaceholder($params, $smarty)
    {
        if (!isset($params['index']) || !is_int($params['index'])) {
            $params['index'] = 0;
        }

        if (is_a($this->_contentView[$params['index']], '\\pff\\AView')) {
            $this->_contentView[$params['index']]->render();
        }
    }
}
