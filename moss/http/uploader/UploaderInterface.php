<?php
namespace moss\http\uploader;

/**
 * Uploader interface
 *
 * @package Moss HTTP
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface UploaderInterface
{

    /**
     * Changes mode for directories and files
     *
     * @param int $dir
     * @param int $file
     *
     * @return mixed
     */
    public function chmod($dir = 0777, $file = 0666);

    /**
     * Sets subdirectories depth
     * Number represents iterations, each iterations is a subdirectory witch name is represented by two chars from file name
     *
     * @param int $depth
     * @param int $length
     *
     * @return Uploader
     */
    public function depth($depth = 0, $length = 2);

    /**
     * If set to true, names will be randomly generated
     * Otherwise name is stripped from non ASCII characters and in lowercase
     *
     * @param bool $random
     *
     * @return Uploader
     */
    public function random($random = false);

    /**
     * Sets max file size to upload
     * Set to 0 for no limit
     *
     * @param int $size
     *
     * @return Uploader
     */
    public function maxSize($size = 0);

    /**
     * Sets extension blacklist
     * If extension is on blacklist file will not be uploaded
     *
     * @param array $blacklist
     *
     * @return Uploader
     */
    public function extBlacklist($blacklist = array());

    /**
     * Sets type blacklist (first part of file mime type)
     * If type is on blacklist file will not be uploaded
     *
     * @param array $blacklist
     *
     * @return Uploader
     */
    public function typeBlacklist($blacklist = array());

    /**
     * Sets mime blacklist
     * If mime is on blacklist file will not be uploaded
     *
     * @param array $blacklist
     *
     * @return Uploader
     */
    public function mimeBlacklist($blacklist = array());

    /**
     * Returns directory where file will be uploaded
     *
     * @param string $dir      main upload directory
     * @param bool   $autoMake if true, directory will be created
     *
     * @return string
     * @throws UploaderException
     */
    public function dir($dir, $autoMake = true);

    /**
     * Uploads all files from $_FILES
     * Returns array containing paths to uploaded files
     *
     * @return array
     */
    public function all();

    /**
     * Uploads files from $_FILES matching names or if no names passed uploads all
     * Returns array containing paths to uploaded files
     *
     * @param array $names
     *
     * @return array
     */
    public function many($names = array());

    /**
     * Uploads one (first) file from $_FILES and returns its path
     * If name is passed - file matching this name will be uploaded
     *
     * @param string $name
     *
     * @return bool|string
     * @throws UploaderException
     */
    public function one($name = null);

    /**
     * Returns target directory name
     *
     * @param string $filename
     *
     * @return string
     */
    public function makeDirName($filename);

    /**
     * Returns target file name depending on set options
     *
     * @param array $node
     *
     * @return string
     */
    public function makeFileName($node);

    /**
     * Returns random file name based on its data and current time
     *
     * @param string $node
     *
     * @return string
     */
    public function makeRandomName($node);
}
