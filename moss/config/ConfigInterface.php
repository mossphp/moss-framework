<?php
namespace moss\config;

/**
 * Config interface
 *
 * @package Moss Config
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ConfigInterface
{

    /**
     * Reads configuration properties from passed array
     *
     * @param array $array
     */
    public function import(array $array);

    /**
     * Returns current stored configuration as array
     *
     * @return array
     */
    public function export();

    /**
     * Returns core variable value
     * If variable is undefined - returns false
     *
     * @param string $var
     *
     * @return mixed
     */
    public function get($var);
}