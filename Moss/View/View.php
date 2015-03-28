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

use Moss\Bag\Bag;

/**
 * Moss view
 * Uses Twig as template engine
 *
 * @package Moss View
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class View extends Bag implements ViewInterface
{
    protected $template;
    protected $pattern;

    /**
     * Creates View instance
     *
     * @param array  $vars
     * @param string $pattern
     */
    public function __construct(array $vars = [], $pattern = '../src/{bundle}/{directory}/View/{file}.php')
    {
        $this->pattern = $pattern;
        parent::__construct($vars);
    }

    /**
     * Assigns template to view
     *
     * @param string $template path to template (supports namespaces)
     *
     * @return ViewInterface
     */
    public function template($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Renders view
     *
     * @return string
     * @throws ViewException
     */
    public function render()
    {
        $file = $this->translate($this->template);

        if (!is_file($file)) {
            throw new ViewException(sprintf('Unable to load template file "%s" (%s)', $this->template, $file));
        }

        ob_start();
        extract($this->storage);
        require $file;

        return ob_get_clean();
    }

    /**
     * Translates view identifier to path
     *
     * @param string $name
     *
     * @return string
     * @throws ViewException
     */
    protected function translate($name)
    {
        preg_match_all('/^(?P<bundle>[^:]+):(?P<directory>[^:]*:)?(?P<file>.+)$/', $name, $matches, \PREG_SET_ORDER);

        foreach (['bundle', 'file'] as $offset) {
            if (empty($matches[0][$offset])) {
                throw new ViewException(sprintf('Invalid or missing "%s" node in view filename "%s"', $offset, $name));
            }
        }

        $placeholders = [
            '{bundle}' => $matches[0]['bundle'],
            '{file}' => $matches[0]['file'],
            '{directory}' => isset($matches[0]['directory']) ? str_replace(':', '\\', $matches[0]['directory']) : null,
        ];

        $file = strtr($this->pattern, $placeholders);
        $file = str_replace(array('\\', '_', '//'), '/', $file);

        return $file;
    }

    /**
     * Renders and returns view as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
