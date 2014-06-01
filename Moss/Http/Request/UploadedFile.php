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

/**
 * Uploaded file representation for Files bag
 *
 * @package  Moss HTTP
 * @author   Michal Wachowski <wachowski.michal@gmail.com>
 */
class UploadedFile extends \SplFileInfo
{
    protected $raw;

    /**
     * Constructor
     *
     * @param array $array
     *
     * @throws UploadedFileException
     */
    public function __construct(array $array)
    {
        $required = array('name', 'type', 'tmp_name', 'error', 'size');
        foreach ($required as $key) {
            if (!array_key_exists($key, $array)) {
                throw new UploadedFileException(sprintf('Missing required array key "%s"', $key));
            }
        }

        $this->raw = $array;
        parent::__construct($array['tmp_name']);
    }

    /**
     * Returns original file name
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->raw['name'];
    }

    /**
     * Moves uploaded file to set path and file name
     *
     * @param string $path
     * @param string $file
     * @param bool   $overwrite
     *
     * @return \SplFileObject
     * @throws UploadedFileException
     */
    public function move($path, $file = null, $overwrite = false)
    {
        if (!$this->isValid()) {
            throw new UploadedFileException($this->getErrorMessage());
        }

        $target = $this->getTarget($path, $file);

        if (!$overwrite && is_file($target)) {
            throw new UploadedFileException(sprintf('Could not move the file "%s" to "%s" ( Target file already exists )', $this->getPathname(), $target));
        }

        if (!move_uploaded_file($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new UploadedFileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
        }

        chmod($target, 0666 & ~umask());

        return new \SplFileObject($target);
    }

    /**
     * Builds file name with path
     *
     * @param string      $path
     * @param null|string $file
     *
     * @return string
     */
    protected function getTarget($path, $file = null)
    {
        if ($file === null) {
            $nArr = explode('.', $this->raw['name']);
            $file = md5(microtime(true) . implode('.', $nArr)) . '.' . array_pop($nArr);
        }

        return rtrim($path, '\\/') . '/' . $file;
    }

    /**
     * Returns raw array containing upload data
     *
     * @return array
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Returns true error occurred
     *
     * @return boolean
     */
    public function hasError()
    {
        return $this->raw['error'] > 0;
    }

    /**
     * Returns error code
     *
     * @return int
     */
    public function getError()
    {
        return $this->raw['error'];
    }

    /**
     * Returns error message
     *
     * @return null|string
     */
    public function getErrorMessage()
    {
        switch ($this->raw['error']) {
            case UPLOAD_ERR_OK:
                return null;
                break;
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in HTML form.';
                break;
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded.';
                break;
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk.';
                break;
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload.';
                break;
            default:
                return 'Unknown error occurred.';
        }
    }

    /**
     * Returns true if upload is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->raw['error'] === UPLOAD_ERR_OK && is_uploaded_file($this->getPathname());
    }
}
