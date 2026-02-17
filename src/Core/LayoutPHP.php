<?php

declare(strict_types=1);

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
class LayoutPHP extends ViewPHP
{
    /**
     * @var AView[]
     */
    private array $_contentView = [];

    public function __construct(string $templateName, ?\pff\App $app = null)
    {
        parent::__construct($templateName);
    }

    /**
     * Adds an AView to the layout queue
     */
    public function addContent(AView $view): void
    {
        $this->_contentView[] = $view;
    }

    /**
     * Show the content in the layout
     *
     * Add "<?php $this->content()?>" in your layout template where you want to
     * display the AView object
     */
    public function content(int $index = 0): void
    {
        if (isset($this->_contentView[$index])) {
            $this->_contentView[$index]->render();
        }
    }

    /**
     * @return AView[]
     */
    public function getContentViews(): array
    {
        return $this->_contentView;
    }
}
