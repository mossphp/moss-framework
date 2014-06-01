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
 * Files bag used by request
 *
 * @package  Moss HTTP
 * @author   Michal Wachowski <wachowski.michal@gmail.com>
 */
class FilesBag extends Bag
{
    private $keys = array('name', 'type', 'tmp_name', 'error', 'size');

    /**
     * Constructor
     *
     * @param array $array
     */
    public function __construct($array)
    {
        $files = array();
        foreach ($array as $field => $data) {
            foreach ($this->keys as $property) {
                $this->property($files, $property, $data[$property], $field);
            }
        }

        foreach ($files as $i => $file) {
            $this->set($i, $file);
        }

        parent::__construct();
    }

    /**
     * Rebuilds $_FILES array
     *
     * @param array $result
     * @param string $property
     * @param array $node
     * @param string $path
     */
    protected function property(&$result, $property, $node, $path = '')
    {
        if (is_array($node)) {
            foreach ($node as $key => $value) {
                $this->property($result, $property, $value, $path . '.' . $key);
            }

            return;
        }

        $result[$path][$property] = $node;
    }

    /**
     * Retrieves offset value
     *
     * @param string $offset
     *
     * @return UploadedFile
     * @throws UploadedFileException
     */
    public function uploaded($offset = null)
    {
        $node = $this->get($offset);

        if (array_keys($node) !== $this->keys) {
            throw new UploadedFileException(sprintf('No uploaded file under offset "%s"', $offset));
        }

        return new UploadedFile($node);
    }
}
