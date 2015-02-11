<?php

namespace pff\Core;
use pff\Abs\AView;

/**
 * Layout system with PHP templates
 *
 * A layout is a specific type of View: it represents a common layout with most
 * of its elements fixed and a few placeholders to render custom views.
 *
 * @author paolo.fagni<at>gmail.com
 */
class LayoutPHP extends ViewPHP {

    /**
     * @var AView[]
     */
    private $_contentView;

    /**
     * Adds an Aview to the layout queue
     *
     * @param AView $view
     */
    public function addContent(AView $view) {
        $this->_contentView[] = $view;
    }

    /**
     * Show the content in the layout
     *
     * Add "<?php $this->content()?>" in your layout template where you want to
     * display the AView object
     *
     * @param int $index
     */
    public function content($index = 0) {
        if(isset($this->_contentView[$index])){
        $this->_contentView[$index]->render();
        }
    }

    public function getContentViews() {
        return $this->_contentView;
    }
}
