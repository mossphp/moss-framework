<?php

/*
 * This file is part of the Moss micro-framework
 *
 * (c) Michal Wachowski <wachowski.michal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Moss\Http\Request;

use Moss\Bag\Bag;

/**
 * Response header bag
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class HeaderBag extends Bag
{
    /**
     * @var array
     */
    protected $languages;

    /**
     * Construct
     *
     * @param array $storage
     */
    public function __construct($storage = array())
    {
        $headers = $this->resolveHeaders($storage);
        $headers = $this->resolveAuth($storage, $headers);

        $this->all(array_change_key_case($headers, CASE_LOWER));

        $this->languages = $this->resolveLanguages();
    }

    /**
     * Resolves header from $_SERVER
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function resolveHeaders(array $parameters)
    {
        $headers = [];
        foreach ($parameters as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[substr($key, 5)] = $value;
            } elseif (in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * Resolves headers data
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function resolveAuth(array $parameters, array $headers)
    {
        if (isset($parameters['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $parameters['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = isset($parameters['PHP_AUTH_PW']) ? $parameters['PHP_AUTH_PW'] : '';
        } else {
            $authorizationHeader = null;
            if (isset($parameters['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['HTTP_AUTHORIZATION'];
            } elseif (isset($parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $parameters['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if ($authorizationHeader !== null && stripos($authorizationHeader, 'basic') === 0) {
                $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
                if (count($exploded) == 2) {
                    list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                }
            }
        }

        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'basic ' . base64_encode($headers['PHP_AUTH_USER'] . ':' . $headers['PHP_AUTH_PW']);
        }

        return $headers;
    }

    /**
     * Retrieves language codes in quality order
     * Builds array containing two letter language codes sorted by quality codes
     *
     * @return array
     */
    protected function resolveLanguages()
    {
        if (!$this->get('accept_language')) {
            return [];
        }

        $codes = $this->extractHeaders();

        $languages = array();
        foreach ($codes as $lang) {
            if (strpos($lang, '-') !== false) {
                $codes = explode('-', $lang);
                $lang = strtolower($codes[0]);
            }

            if (in_array($lang, $languages)) {
                continue;
            }

            $languages[] = $lang;
        }

        return $languages;
    }

    /**
     * Extracts language codes from header
     *
     * @return array
     */
    protected function extractHeaders()
    {
        $codes = array();

        $header = array_filter(explode(',', (string) $this->get('accept_language')));
        foreach ($header as $value) {
            if (preg_match('/;\s*(q=.*$)/', $value, $match)) {
                $quality = (float) substr(trim($match[1]), 2) * 10;
                $value = trim(substr($value, 0, -strlen($match[0])));
            } else {
                $quality = 1;
            }

            if (0 < $quality) {
                $codes[$quality] = trim($value);
            }
        }

        sort($codes);

        return $codes;
    }

    /**
     * Returns array containing languages from Accept-Language sorted by quality (priority)
     *
     * @return array
     */
    public function languages()
    {
        return $this->languages;
    }

    /**
     * Retrieves offset value
     *
     * @param string $offset
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($offset = null, $default = null)
    {
        if ($offset === null) {
            return $this->all();
        }

        $offset = str_replace('-', '_', strtolower($offset));

        return $this->getFromArray($this->storage, explode(self::SEPARATOR, $offset), $default);
    }
}
