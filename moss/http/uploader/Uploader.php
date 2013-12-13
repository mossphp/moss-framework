<?php
namespace moss\http\uploader;

use moss\http\request\RequestInterface;

/**
 * Upload
 * Handles uploads from single field, single field with multiple values or multiple fields
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Uploader implements UploaderInterface
{
    protected $request;

    protected $files = array();
    protected $names = array();
    protected $dir;

    private $chmod_dir;
    private $chmod_file;

    private $depth = 0;
    private $depthLen = 2;
    private $random = false;

    private $maxSize = array();
    private $extBlacklist = array();
    private $typeBlacklist = array();
    private $mimeBlacklist = array();

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param string           $dir
     * @param bool             $autoCreate
     */
    public function __construct(RequestInterface $request, $dir, $autoCreate = true)
    {
        $this->request = & $request;
        $this->names = $this->resolveNames($this->request->files());
        $this->dir = $this->dir($dir, $autoCreate);
    }

    /**
     * Changes mode for directories and files
     *
     * @param int $dir
     * @param int $file
     *
     * @return mixed
     */
    public function chmod($dir = 0777, $file = 0666)
    {
        $this->chmod_dir = $dir;
        $this->chmod_file = $file;
    }


    /**
     * Sets subdirectories depth
     * Number represents iterations, each is a subdirectory witch name is represented by $length chars from file name
     *
     * @param int $depth
     * @param int $length
     *
     * @return Uploader
     */
    public function depth($depth = 0, $length = 2)
    {
        $this->depth = (int) $depth;
        $this->depthLen = (int) $length;

        return $this;
    }

    /**
     * If set to true, names will be randomly generated
     * Otherwise name is stripped from non ASCII characters and in lowercase
     *
     * @param bool $random
     *
     * @return Uploader
     */
    public function random($random = false)
    {
        $this->random = (bool) $random;

        return $this;
    }

    /**
     * Sets max file size to upload
     * Set to 0 for no limit
     *
     * @param int $size
     *
     * @return Uploader
     */
    public function maxSize($size = 0)
    {
        $this->maxSize = (int) $size;

        return $this;
    }

    /**
     * Sets extension blacklist
     * If extension is on blacklist file will not be uploaded
     *
     * @param array $blacklist
     *
     * @return Uploader
     */
    public function extBlacklist($blacklist = array())
    {
        $this->extBlacklist = (array) $blacklist;

        return $this;
    }

    /**
     * Sets type blacklist (first part of file mime type)
     * If type is on blacklist file will not be uploaded
     *
     * @param array $blacklist
     *
     * @return Uploader
     */
    public function typeBlacklist($blacklist = array())
    {
        $this->typeBlacklist = (array) $blacklist;

        return $this;
    }

    /**
     * Sets mime blacklist
     * If mime is on blacklist file will not be uploaded
     *
     * @param array $blacklist
     *
     * @return Uploader
     */
    public function mimeBlacklist($blacklist = array())
    {
        $this->mimeBlacklist = (array) $blacklist;

        return $this;
    }

    /**
     * Returns directory where file will be uploaded
     *
     * @param string $dir      main upload directory
     * @param bool   $autoMake if true, directory will be created
     *
     * @return string
     * @throws UploaderException
     */
    public function dir($dir, $autoMake = true)
    {
        $dir = str_replace('\\', '/', $dir);

        if (!is_dir($dir)) {
            if ($autoMake) {
                mkdir($dir, $this->chmod_dir, true);
            }

            if (!is_dir($dir)) {
                throw new UploaderException('Directory ' . $dir . ' does not exists');
            }
        }

        if (fileperms($dir) !== $this->chmod_dir) {
            chmod($dir, $this->chmod_dir);
        }

        if (!is_writeable($dir)) {
            throw new UploaderException('Directory ' . $dir . ' is not writable');
        }

        return $dir;
    }

    /**
     * Resolves file field names
     *
     * @param array $array
     * @param array $keys
     *
     * @return array
     */
    private function resolveNames($array, $keys = array())
    {
        $names = array();
        $attributes = array('name', 'type', 'tmp_name', 'error', 'error_text', 'size');

        if (!is_array($array)) {
            $names[] = implode('.', $keys);

            return $names;
        }

        if (array_keys($array) == $attributes) {
            $names[] = implode('.', $keys);

            return $names;
        }

        foreach ($array as $key => $sub) {
            $names = array_merge($names, $this->resolveNames($sub, array_merge($keys, array($key))));
        }

        return $names;
    }

    /**
     * Uploads all files from $_FILES
     * Returns array containing paths to uploaded files
     *
     * @return array
     */
    public function all()
    {
        return $this->many();
    }

    /**
     * Uploads files from $_FILES matching names or if no names passed uploads all
     * Returns array containing paths to uploaded files
     *
     * @param array $names
     *
     * @return array
     */
    public function many($names = array())
    {
        if (empty($names)) {
            $names = $this->names;
        }

        $result = array();
        foreach ($names as $key) {
            $result[$key] = $this->one($key);
        }

        return $result;
    }

    /**
     * Uploads one (first) file from $_FILES and returns its path
     * If name is passed - file matching this name will be uploaded
     *
     * @param string $name
     *
     * @return bool|string
     * @throws UploaderException
     */
    public function one($name = null)
    {
        if ($name === null) {
            $name = reset($this->names);
        }

        $file = $this->request->files($name);
        if (empty($file)) {
            return false;
        }

        if ($file['error'] != 0) {
            return false;
        }

        $trg = $this->makeFileName($file);
        $dir = $this->dir($this->depth > 0 ? $this->makeDirName($trg) : $this->dir);

        $trg = $dir . $trg;

        if (!move_uploaded_file($file['tmp_name'], $trg)) {
            throw new UploaderException('Error occurred during moving uploaded file');
        }

        chmod($trg, $this->chmod_file);

        return array($file['name'], $trg);
    }

    /**
     * Returns target directory name
     *
     * @param string $filename
     *
     * @return string
     */
    public function makeDirName($filename)
    {
        $dir = array(rtrim($this->dir, '/'));
        $filename = explode('.', basename($filename));
        array_pop($filename);
        $filename = implode($filename);

        for ($i = 0; $i < $this->depth; $i++) {
            if (strlen($filename) >= $i * $this->depthLen + $this->depthLen) {
                $dir[] = substr($filename, $i * $this->depthLen, $this->depthLen);
            }
        }

        return rtrim(implode('/', $dir), '/') . '/';
    }

    /**
     * Returns target file name depending on set options
     *
     * @param array $node
     *
     * @return string
     */
    public function makeFileName($node)
    {
        if ($this->random) {
            return $this->makeRandomName($node);
        }

        $nArr = explode('.', basename($node['name']));
        $ext = array_pop($nArr);

        return $this->strip(implode('.', $nArr)) . '.' . $this->strip($ext);
    }

    /**
     * Returns random file name based on its data and current time
     *
     * @param string $node
     *
     * @return string
     */
    public function makeRandomName($node)
    {
        $nArr = explode('.', basename($node['name']));
        $ext = array_pop($nArr);

        return md5(microtime(true) . implode('', $node)) . '.' . $this->strip($ext);
    }

    /**
     * Strips string from non ASCII characters, spaces and change it to lowercase
     * Optional separator replacing spaces
     *
     * @param string $string
     * @param string $separator
     *
     * @return string
     */
    protected function strip($string, $separator = '-')
    {
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        $string = strtolower($string);
        $string = preg_replace('#[^\w\. \-]+#i', null, $string);
        $string = preg_replace('/[ -]+/', $separator, $string);
        $string = trim($string, '-');

        return $string;
    }
}
