<?php

namespace pff\Iface;

interface IRenderable
{
    /**
     * Sets a value to be passed to a View
     *
     * @param string $name
     * @param mixed $value
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
