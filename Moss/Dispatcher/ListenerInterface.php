<?php
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
