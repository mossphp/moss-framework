<?php
namespace moss\dispatcher;

use moss\container\ContainerInterface;

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
     * @param ContainerInterface $Container
     * @param mixed              $Subject
     * @param mixed              $message
     *
     * @return mixed
     */
    public function get(ContainerInterface $Container, $Subject = null, $message = null);
}