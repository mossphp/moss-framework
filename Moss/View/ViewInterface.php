<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\View;

use Moss\Bag\BagInterface;

/**
 * Moss ViewInterface
 *
 * @package Moss View
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ViewInterface extends BagInterface
{

    /**
     * Assigns template to view
     *
     * @param string $template path to template (supports namespaces)
     *
     * @return ViewInterface
     */
    public function template($template);

    /**
     * Renders view
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render();

    /**
     * Renders and returns view as string
     *
     * @return string
     */
    public function __toString();
}
