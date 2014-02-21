<?php
namespace Moss\Security;

/**
 * Security crypt interface
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface CryptInterface
{
    const SDES = 'sdes';
    const EDES = 'edes';
    const MD5 = 'md5';
    const BLOWFISH = 'blowfish';
    const SHA256 = 'sha256';
    const SHA512 = 'sha512';

    /**
     * Crypts passed string with set algorithm
     *
     * @param string $password
     *
     * @return string
     */
    public function hash($password);

    /**
     * Returns true if password and hashed are equal
     *
     * @param $password
     * @param $hashed
     *
     * @return bool
     */
    public function compare($password, $hashed);
}
