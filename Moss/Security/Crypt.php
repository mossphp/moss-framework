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
 * Security crypt
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Crypt implements CryptInterface
{
    protected $algorithm;
    protected $iterations;
    protected $salt;

    /**
     * Constructor

     */
    public function __construct($algorithm = self::BLOWFISH, $iterations = 7, $salt = null)
    {
        if (!in_array($algorithm, array(self::SDES, self::EDES, self::MD5, self::BLOWFISH, self::SHA256, self::SHA512))) {
            throw new SecurityException('Unknown crypt method');
        }

        $this->algorithm = $algorithm;
        $this->iterations = (int) $iterations;
        $this->salt = $salt !== null ? $salt : null;
    }

    /**
     * Crypts passed string with set algorithm
     *
     * @param string $password
     *
     * @return string
     */
    public function hash($password)
    {
        return crypt($password, $this->salt ? $this->salt : $this->generateSalt());
    }

    /**
     * Returns true if password and hashed are equal
     *
     * @param $password
     * @param $hashed
     *
     * @return bool
     */
    public function compare($password, $hashed)
    {
        if (strlen($password) > 4096) {
            return false;
        }

        return $hashed === crypt($password, $hashed);
    }

    /**
     * Generates salt
     *
     * @return string
     * @throws SecurityException
     */
    protected function generateSalt()
    {
        $r = array();
        switch ($this->algorithm) {
            case self::SDES:
                for ($i = 0; $i < 2; $i++) {
                    $r[] = $this->generateRandomBlock();
                }

                return substr(base64_encode(implode($r)), 0, 2);
                break;
            case self::EDES:
                for ($i = 0; $i < 4; $i++) {
                    $r[] = $this->generateRandomBlock();
                }

                return '_' . str_pad($this->iterations, 4, '.', STR_PAD_RIGHT) . substr(base64_encode(implode($r)), 0, 4);
                break;
            case self::MD5:
                for ($i = 0; $i < 12; $i++) {
                    $r[] = $this->generateRandomBlock();
                }

                return '$1$' . substr(base64_encode(implode($r)), 0, 8) . '$';
                break;
            case self::BLOWFISH:
                for ($i = 0; $i < 32; ++$i) {
                    $r[] = $this->generateRandomBlock();
                }
                $r[] = substr(microtime(), 2, 6);

                return '$2a$' . str_pad($this->iterations, 2, '0', STR_PAD_RIGHT) . '$' . strtr(substr(base64_encode(implode($r)), 0, 25), array('+' => '.')) . '$';
                break;
            case self::SHA256:
                for ($i = 0; $i < 32; ++$i) {
                    $r[] = $this->generateRandomBlock();
                }
                $r[] = substr(microtime(), 2, 6);

                return '$5$rounds=' . str_pad($this->iterations, 4, '0', STR_PAD_RIGHT) . '$' . strtr(substr(base64_encode(implode($r)), 0, 25), array('+' => '.')) . '$';
                break;
            case self::SHA512:
                for ($i = 0; $i < 32; ++$i) {
                    $r[] = $this->generateRandomBlock();
                }
                $r[] = substr(microtime(), 2, 6);

                return '$6$rounds=' . str_pad($this->iterations, 4, '0', STR_PAD_RIGHT) . '$' . strtr(substr(base64_encode(implode($r)), 0, 25), array('+' => '.')) . '$';
                break;
            default:
                throw new SecurityException('Unknown crypt method');
        }
    }

    /**
     * Generates random block for salt
     *
     * @return string
     */
    protected function generateRandomBlock()
    {
        return pack('S', mt_rand(0, 0xffff));
    }
}
