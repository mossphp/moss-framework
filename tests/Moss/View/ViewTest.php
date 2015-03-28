<?php
namespace Moss\View;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Moss\View\ViewException
     * @expectedExceptionMessage Invalid or missing "bundle" node in view filename "foo"
     */
    public function testMissingRequiredPatternValue()
    {
        $view = new View('/{bundle}.{file}.phtml');
        $view->template('foo');
        $view->render();
    }

    /**
     * @expectedException \Moss\View\ViewException
     * @expectedExceptionMessage Unable to load template file "foo:bar:yada"
     */
    public function testMissingTemplateFile()
    {
        $view = new View('/{bundle}.{file}.phtml');
        $view->template('foo:bar:yada');
        $view->render();
    }

    public function testTemplate()
    {
        $view = new View(__DIR__ . '/{bundle}.{file}.phtml', ['var' => 'Yup!']);
        $view->template('foo:bar');
        $this->assertEquals('Renders template? Yup!', $view->render());
    }

    /**
     * @dataProvider setProvider
     */
    public function testGetSet($key, $value, $result)
    {
        $view = new View(__DIR__ . '/{bundle}.{file}.phtml');
        $view->template('foo:bar')
            ->set($key, $value);

        $this->assertEquals($result, $view->get());
    }

    public function setProvider()
    {
        return [
            ['foo', 1, ['foo' => 1]],
            ['bar', 'lorem', ['bar' => 'lorem']],
            ['yada', ['yada' => 'yada'], ['yada' => ['yada' => 'yada']]],
            ['dada', new \stdClass(), ['dada' => new \stdClass()]]
        ];
    }

    public function testRender()
    {
        $view = new View(__DIR__ . '/{bundle}.{file}.phtml', ['var' => 'Yup']);
        $result = $view->template('foo:bar')
            ->render();

        $this->assertEquals('Renders template? Yup', $result);
    }

    public function testToString()
    {
        $view = new View(__DIR__ . '/{bundle}.{file}.phtml', ['var' => 'Yup']);
        $result = $view->template('foo:bar')
            ->__toString();

        $this->assertEquals('Renders template? Yup', $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetUnset($offset, $value)
    {
        $view = new View('/{bundle}.{file}.phtml');
        $view[$offset] = $value;
        unset($view[$offset]);
        $this->assertEquals(0, $view->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetSet($offset, $value)
    {
        $view = new View('/{bundle}.{file}.phtml');
        $view[$offset] = $value;
        $this->assertEquals($value, $view[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetGetEmpty($offset)
    {
        $view = new View('/{bundle}.{file}.phtml');
        $this->assertNull(null, $view[$offset]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetSetWithoutKey($value)
    {
        $view = new View('/{bundle}.{file}.phtml');
        $view[] = $value;
        $this->assertEquals($value, $view[0]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOffsetExists($offset, $value)
    {
        $view = new View('/{bundle}.{file}.phtml');
        $view[$offset] = $value;
        $this->assertTrue(isset($view[$offset]));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCount($offset, $value)
    {
        $view = new View('/{bundle}.{file}.phtml');
        $view[1] = $offset;
        $view[2] = $value;
        $this->assertEquals(2, $view->count());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIterator($offset, $value)
    {
        $view = new View('/{bundle}.{file}.phtml');
        $view[$offset] = $value;

        foreach ($view as $key => $val) {
            $this->assertEquals($key, $offset);
            $this->assertEquals($val, $value);
        }
    }

    public function dataProvider()
    {
        return [
            ['foo', 1, ['foo' => 1]],
            ['bar', 'lorem', ['bar' => 'lorem']],
            ['yada', ['yada' => 'yada'], ['yada' => ['yada' => 'yada']]],
            ['dada', new \stdClass(), ['dada' => new \stdClass()]],
            ['foo.bar', 'yada', ['foo' => ['bar' => 'yada']], ['foo' => []]]
        ];
    }
}
