<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Security;

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
     * @param string $right
     *
     * @return bool
     */
    public function hasRight($right);
}
