<?php
namespace moss\security;

/**
 * Security User interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface UserInterface
{

    /**
     * Returns user identifier
     *
     * @return int|string
     */
    public function identify();

    /**
     * Returns all roles as an array
     *
     * @return array
     */
    public function getRoles();

    /**
     * Returns true if user has role
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole($role);

    /**
     * Returns all role access as an array
     *
     * @return array
     */
    public function getRights();

    /**
     * Returns true if user has access
     *
     * @param string $access
     *
     * @return bool
     */
    public function hasRight($access);
}
