<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Dispatcher;

use Moss\Container\ContainerInterface;

/**
 * Event dispatchers listener interface
 *
 * @package Moss Dispatcher
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ListenerInterface
{

    /**
     * Returns component instance
     *
     * @param ContainerInterface $container
     * @param mixed              $subject
     * @param mixed              $message
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $subject = null, $message = null);

    /**
     * Returns component instance
     *
     * @param ContainerInterface $container
     * @param mixed              $subject
     * @param mixed              $message
     *
     * @return mixed
     */
    public function get(ContainerInterface $container, $subject = null, $message = null);
}
