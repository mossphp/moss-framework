<?php
namespace moss\loader;

class MockLoader extends Loader
{
    protected $files;

    public function addNamespace($namespace, $paths)
    {
        $namespace = rtrim($namespace, '\\');

        foreach ((array) $paths as $path) {
            if (!isset($this->namespaces[(string) $namespace])) {
                $this->namespaces[(string) $namespace] = array();
            }

            $length = strlen($path);
            if ($length == 0 || $path[$length - 1] != '/') {
                $path .= '/';
            }

            $this->namespaces[(string) $namespace][] = $path;
        }

        return $this;
    }

    public function setFiles(array $files)
    {
        foreach ($files as &$file) {
            $file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file);
            unset($file);
        }

        $this->files = $files;
    }

    protected function requireFile($file)
    {
        $file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file);

        return in_array($file, $this->files);
    }
}

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Loader */
    protected $loader;

    protected function setUp()
    {
        $this->loader = new MockLoader();

        $this->loader->setFiles(
                     array(
                          // moss psr 0
                          '/../moss/http/response/HeaderBag.php',
                          '/../src/moss/sample/controller/SampleController.php',
                          '/../vendor/psr/log/Psr/Log/LogLevel.php',

                          // riu
                          '/hanariu/controller/Editor/IndexController.php',

                          // specification
                          '/path/to/package/foo-bar/src/Baz.php',
                          '/path/to/package/foo-bar/src/Qux/Quux.php',
                          '/path/to/package/foo-bar/test/BazTest.php',
                          '/path/to/package/foo-bar/test/Qux/QuuxTest.php',

                          // original
                          '/vendor/foo.bar/src/ClassName.php',
                          '/vendor/foo.bar/src/DoomClassName.php',
                          '/vendor/foo.bar/tests/ClassNameTest.php',
                          '/vendor/foo.bardoom/src/ClassName.php',
                          '/vendor/foo.bar.baz.dib/src/ClassName.php',
                          '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php',

                          // pear
                          '/vendor/twig/extensions/lib/Twig/Extensions/Extension/Text.php',
                          '/vendor/twig/bridge/lib/Twig/Bridge/Extension/Resource.php',
                          '/vendor/twig/twig/lib/Twig/Environment.php'
                     )
        );

// moss psr-0
        $this->loader->addNamespace('moss', '/../');
        $this->loader->addNamespace(null, '/../src/');
        $this->loader->addNamespace('Psr/Log', '/../vendor/psr/log/');

// riu
        $this->loader->addNamespace('Controller\\Admin\\', '/hanariu/controller/');

// specification
        $this->loader->addNamespace('Foo\Bar', '/path/to/package/foo-bar/src/');
        $this->loader->addNamespace('Foo\Bar', '/path/to/package/foo-bar/test/');

// original test
        $this->loader->addNamespace('Foo\Bar', array('/vendor/foo.bar/src', '/vendor/foo.bar/tests'));
        $this->loader->addNamespace('Foo\BarDoom', '/vendor/foo.bardoom/src');
        $this->loader->addNamespace('Foo\Bar\Baz\Dib', '/vendor/foo.bar.baz.dib/src');
        $this->loader->addNamespace('Foo\Bar\Baz\Dib\Zim\Gir', '/vendor/foo.bar.baz.dib.zim.gir/src');

// pear
        $this->loader->addNamespace('Twig\Extensions', '/vendor/twig/extensions/lib');
        $this->loader->addNamespace('Twig\Bridge', '/vendor/twig/bridge/lib');
        $this->loader->addNamespace('Twig', '/vendor/twig/twig/lib');
    }

    /**
     * @dataProvider existingProvider
     */
    public function testExistingFile($className, $expected)
    {
        $this->assertSame($expected, $this->loader->findFile($className), $className);
    }

    public function existingProvider()
    {
        return array(
            // psr-0
            array('moss\http\response\HeaderBag', '/../moss/http/response/HeaderBag.php'),
            array('moss\sample\controller\SampleController', '/../src/moss/sample/controller/SampleController.php'),
            array('Psr\Log\LogLevel', '/../vendor/psr/log/Psr/Log/LogLevel.php'),

            // riu
            array('Controller\Admin\Editor\IndexController', '/hanariu/controller/Editor/IndexController.php'),

            // specification
            array('Foo\Bar\Baz', '/path/to/package/foo-bar/src/Baz.php'),
            array('Foo\Bar\Qux\Quux', '/path/to/package/foo-bar/src/Qux/Quux.php'),
            array('Foo\Bar\BazTest', '/path/to/package/foo-bar/test/BazTest.php'),
            array('Foo\Bar\Qux\QuuxTest', '/path/to/package/foo-bar/test/Qux/QuuxTest.php'),

            // original test
            array('Foo\Bar\ClassName', '/vendor/foo.bar/src/ClassName.php'),
            array('Foo\Bar\ClassNameTest', '/vendor/foo.bar/tests/ClassNameTest.php'),

            // pear
            array('Twig_Extensions_Extension_Text', '/vendor/twig/extensions/lib/Twig/Extensions/Extension/Text.php'),
            array('Twig_Bridge_Extension_Resource', '/vendor/twig/bridge/lib/Twig/Bridge/Extension/Resource.php'),
            array('Twig_Environment', '/vendor/twig/twig/lib/Twig/Environment.php'),
        );
    }

    public function testMissingFile()
    {
        $actual = $this->loader->findFile('No_Vendor\No_Package\NoClass');
        $this->assertFalse($actual);
    }

    public function testDeepFile()
    {
        $actual = $this->loader->findFile('Foo\Bar\Baz\Dib\Zim\Gir\ClassName');
        $expected = '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php';
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider confusionProvider
     */
    public function testConfusion($className, $expected)
    {
        $this->assertSame($expected, $this->loader->findFile($className));
    }

    public function confusionProvider()
    {
        return array(
            array('Foo\Bar\DoomClassName', '/vendor/foo.bar/src/DoomClassName.php'),
            array('Foo\BarDoom\ClassName', '/vendor/foo.bardoom/src/ClassName.php')
        );
    }
}
 