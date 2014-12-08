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

function is_uploaded_file($file) {
    return is_file($file);
}

function move_uploaded_file($filename, $destination) {
    return is_file($destination);
}

class UploadedFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Moss\Http\Request\UploadedFileException
     * @expectedExceptionMessage Missing required array key
     */
    public function testMissingKey()
    {
        $up = new UploadedFile([]);
        $up->getOriginalName();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOriginalName($data)
    {
        $up = new UploadedFile($data);
        $this->assertEquals($data['name'], $up->getOriginalName());
    }

    public function testMove()
    {
        $data = [
            'name' => __FILE__,
            'type' => 'text/plain',
            'tmp_name' => __FILE__,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize(__FILE__)
        ];

        $up = new UploadedFile($data);
        $up->move(__DIR__, 'UploadedFileTest.php', true);
    }

    /**
     * @expectedException \Moss\Http\Request\UploadedFileException
     * @expectedExceptionMessage Upload was not successful -
     */
    public function testMoveWhenUploadWasNotSuccessful()
    {
        $data = [
            'name' => __FILE__,
            'type' => 'text/plain',
            'tmp_name' => __FILE__,
            'error' => UPLOAD_ERR_CANT_WRITE,
            'size' => filesize(__FILE__)
        ];

        $up = new UploadedFile($data);
        $up->move(__DIR__, 'UploadedFileTest.php', true);
    }

    /**
     * @expectedException \Moss\Http\Request\UploadedFileException
     * @expectedExceptionMessage Could not move the file
     */
    public function testMoveWhenTargetFileAlreadyExists()
    {
        $data = [
            'name' => __FILE__,
            'type' => 'text/plain',
            'tmp_name' => __FILE__,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize(__FILE__)
        ];

        $up = new UploadedFile($data);
        $up->move(__DIR__, 'UploadedFileTest.php', false);
    }

    /**
     * @expectedException \Moss\Http\Request\UploadedFileException
     * @expectedExceptionMessage Could not move the file
     */
    public function testMoveInternalError()
    {
        $data = [
            'name' => __FILE__,
            'type' => 'text/plain',
            'tmp_name' => __FILE__,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize(__FILE__)
        ];

        $up = new UploadedFile($data);
        $up->move(__DIR__, null, false);
    }

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

    public function testIsValid()
    {
        $data = [
            'name' => __FILE__,
            'type' => 'text/plain',
            'tmp_name' => __FILE__,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize(__FILE__)
        ];

        $up = new UploadedFile($data);
        $this->assertEquals(true, $up->isValid());
    }

    public function dataProvider()
    {
        return [
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 0,
                    'size' => 123
                ],
                null
            ],
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 1,
                    'size' => 0
                ],
                'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            ],
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 2,
                    'size' => 0
                ],
                'The uploaded file exceeds the MAX_FILE_SIZE directive specified in HTML form.',
            ],
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 3,
                    'size' => 0
                ],
                'The uploaded file was only partially uploaded.',
            ],
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 4,
                    'size' => 0
                ],
                'No file was uploaded.',
            ],
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 5,
                    'size' => 0
                ],
                'Unknown error occurred.',
            ],
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 6,
                    'size' => 0
                ],
                'Missing a temporary folder.',
            ],
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 7,
                    'size' => 0
                ],
                'Failed to write file to disk.',
            ],
            [
                [
                    'name' => 'bar.txt',
                    'type' => 'text/plain',
                    'tmp_name' => 'whatever2',
                    'error' => 8,
                    'size' => 0
                ],
                'A PHP extension stopped the file upload.',
            ]
        ];
    }
}
