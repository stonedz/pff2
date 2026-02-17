<?php

declare(strict_types=1);

namespace pff\Iface;

interface IRenderable
{
    /**
     * Sets a value to be passed to a View
     *
     * @param string $name
     * @return void
     */
    public function set(string $name, mixed $value): void;

    /**
     * Renders the view
     *
     * @return void
     */
    public function render(): void;

    /**
     * Returns the rendered HTML without output to browser
     *
     * @return string
     */
    public function renderHtml(): string;
}
