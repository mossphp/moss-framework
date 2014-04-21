<?php

/*
* This file is part of the moss-framework package
*
* (c) Michal Wachowski <wachowski.michal@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Moss\Http\Request;


class UploadedFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testOriginalName($data)
    {
        $up = new UploadedFile($data);
        $this->assertEquals($data['name'], $up->getOriginalName());
    }

//    /**
//     * @dataProvider dataProvider
//     */
//    public function testMove($data)
//    {
//        $this->markTestSkipped();
//    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetRaw($data)
    {
        $up = new UploadedFile($data);
        $this->assertEquals($data, $up->getRaw());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHasError($data)
    {
        $up = new UploadedFile($data);
        $this->assertEquals($data['error'] > 0, $up->hasError());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testError($data)
    {
        $up = new UploadedFile($data);
        $this->assertEquals($data['error'], $up->getError());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testErrorMessage($data, $msg)
    {
        $up = new UploadedFile($data);
        $this->assertEquals($msg, $up->getErrorMessage());
    }

//    /**
//     * @dataProvider dataProvider
//     */
//    public function testIsValid($data)
//    {
//        $this->markTestSkipped();
//    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 0,
                    'size' => 123
                ),
                null
            ),
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 1,
                    'size' => 0
                ),
                'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            ),
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 2,
                    'size' => 0
                ),
                'The uploaded file exceeds the MAX_FILE_SIZE directive specified in HTML form.',
            ),
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 3,
                    'size' => 0
                ),
                'The uploaded file was only partially uploaded.',
            ),
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 4,
                    'size' => 0
                ),
                'No file was uploaded.',
            ),
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 5,
                    'size' => 0
                ),
                'Unknown error occurred.',
            ),
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 6,
                    'size' => 0
                ),
                'Missing a temporary folder.',
            ),
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 7,
                    'size' => 0
                ),
                'Failed to write file to disk.',
            ),
            array(
                array(
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 8,
                    'size' => 0
                ),
                'A PHP extension stopped the file upload.',
            )
        );
    }
}
