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
     * Returns all roles as an array
     *
     * @return array
     */
    public function getRole();

    /**
     * Returns true if user has role
     *
     * @param string $roleIdentifier
     *
     * @return bool
     */
    public function hasRole($roleIdentifier);

    /**
     * Returns all role access as an array
     *
     * @return array
     */
    public function getAccess();

    /**
     * Returns true if user has access
     *
     * @param string $accessIdentifier
     *
     * @return bool
     */
    public function hasAccess($accessIdentifier);
}