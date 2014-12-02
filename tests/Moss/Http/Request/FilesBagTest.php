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

class FilesBagTest extends \PHPUnit_Framework_TestCase
{

    public function testFILESRebuild()
    {
        $data = [
            'foo' => [
                'name' => ['bar' => 'bar.txt'],
                'type' => ['bar' => 'text/plain'],
                'tmp_name' => ['bar' => 'whatever2'],
                'error' => ['bar' => 0],
                'size' => ['bar' => 123]
            ]
        ];

        $expected = [
            'name' => 'bar.txt',
            'type' => 'text/plain',
            'tmp_name' => 'whatever2',
            'error' => 0,
            'size' => 123
        ];

        $bag = new FilesBag($data);
        $this->assertEquals($expected, $bag->get('foo.bar'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetSet($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $this->assertEquals($expected, $bag->get($offset));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetAll($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $this->assertEquals($data, $bag->get());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHasWithoutParam($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $this->assertTrue($bag->has());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHas($offset, $data)
    {
        $bag = new FilesBag($data);
        $this->assertTrue($bag->has($offset));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAll($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $this->assertEquals($data, $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAllReplace($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $bag->all([$offset => $expected]);
        $this->assertEquals([$offset => $expected], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemove($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $this->assertEquals($data, $bag->all());

        $bag->remove($offset . '.name');
        unset($data[$offset]['name']);
        $this->assertEquals($data, $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRemoveAll($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $this->assertEquals($data, $bag->all());

        $bag->remove();
        $this->assertEquals([], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testReset($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $this->assertEquals($data, $bag->all());
        $bag->reset();
        $this->assertEquals([], $bag->all());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetUnset($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $bag->reset();
        $bag[$offset] = $expected;
        $this->assertEquals($expected, $bag->offsetGet($offset));
        unset($bag[$offset]);
        $this->assertEquals(0, $bag->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetSet($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $bag->reset();
        $bag[$offset] = $expected;
        $this->assertEquals($expected, $bag[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetEmpty($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $bag->reset();
        $this->assertNull(null, $bag[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetSetWithoutKey($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $bag[] = $expected;
        $this->assertEquals($expected, $bag[0]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetExists($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $bag[$offset] = $expected;
        $this->assertTrue(isset($bag[$offset]));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIterator($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $bag[$offset] = $expected;

        foreach ($bag as $key => $val) {
            $this->assertEquals($key, $offset);
            $this->assertEquals($val, $expected);
        }
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCount($offset, $data, $expected)
    {
        $bag = new FilesBag($data);
        $bag[1] = $expected;
        $bag[2] = $expected;
        $this->assertEquals(count($data) + 2, $bag->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testUploaded($offset, $data, $expected, $errorMessage)
    {
        $bag = new FilesBag($data);

        $node = $bag->uploaded($offset);

        $this->assertInstanceOf('\Moss\Http\Request\UploadedFile', $node);

        $this->assertEquals($expected['name'], $node->getOriginalName());
        $this->assertEquals($expected['error'], $node->getError());
        $this->assertEquals($errorMessage, $node->getErrorMessage());
    }

    public function dataProvider()
    {
        return [
            [
                'bar0',
                [
                    'bar0' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 0,
                        'size' => 123
                    ]
                ],
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
                'bar1',
                [
                    'bar1' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 1,
                        'size' => 0
                    ]
                ],
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
                'bar2',
                [
                    'bar2' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 2,
                        'size' => 0
                    ]
                ],
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
                'bar3',
                [
                    'bar3' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 3,
                        'size' => 0
                    ]
                ],
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
                'bar4',
                [
                    'bar4' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 4,
                        'size' => 0
                    ]
                ],
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
                'bar5',
                [
                    'bar5' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 5,
                        'size' => 0
                    ]
                ],
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
                'bar6',
                [
                    'bar6' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 6,
                        'size' => 0
                    ]
                ],
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
                'bar7',
                [
                    'bar7' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 7,
                        'size' => 0
                    ]
                ],
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
                'bar8',
                [
                    'bar8' => [
                        'name' => 'bar.txt',
                        'type' => 'text/plain',
                        'tmp_name' => 'whatever2',
                        'error' => 8,
                        'size' => 0
                    ]
                ],
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

    /**
     * @expectedException \Moss\Http\Request\UploadedFileException
     * @expectedExceptionMessage No uploaded file under offset
     */
    public function testAccessToNonExistingKey()
    {
        $bag = new FilesBag([]);
        $bag->uploaded('foo');
    }
}
