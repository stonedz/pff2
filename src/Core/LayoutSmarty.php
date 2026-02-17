<?php

declare(strict_types=1);

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
    public function __construct(string $tempalteName, \pff\App $app)
    {
        parent::__construct($tempalteName, $app);
        $this->_smarty->registerPlugin('function', 'content', $this->smarty_plugin_contentPlaceholder(...));
    }

    /**
     * @var AView[]
     */
    private array $_contentView = [];

    /**
     * Adds an AView to the layout queue
     */
    public function addContent(AView $view): void
    {
        $this->_contentView[] = $view;
    }

    public function smarty_plugin_contentPlaceholder(array $params, \Smarty $smarty): void
    {
        if (!isset($params['index']) || !is_int($params['index'])) {
            $params['index'] = 0;
        }

        if (is_a($this->_contentView[$params['index']], '\\pff\\AView')) {
            $this->_contentView[$params['index']]->render();
        }
    }
}
