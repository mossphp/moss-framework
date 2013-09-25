<?php
namespace moss\logger;

use Psr\Log\LogLevel;

class LoggerTest extends \PHPUnit_Framework_TestCase
{

    protected $file = 'test_log.txt';

    public function setUp()
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function tearDown()
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testImplements()
    {
        $this->assertInstanceOf('Psr\Log\LoggerInterface', new Logger());
    }

    /**
     * @dataProvider provideLevelsAndMessages
     */
    public function testLogsAtAllLevels($level, $message)
    {
        $Logger = new Logger();
        $Logger->{$level}($message, array('user' => 'Bob'));
        $Logger->log($level, $message, array('user' => 'Bob'));

        $expected = array(
            $level . ' message of level ' . $level . ' with context: Bob',
            $level . ' message of level ' . $level . ' with context: Bob',
        );

        $this->assertEquals($expected, $Logger->get(false));
    }

    public function provideLevelsAndMessages()
    {
        return array(
            LogLevel::EMERGENCY => array(LogLevel::EMERGENCY, 'message of level emergency with context: {user}'),
            LogLevel::ALERT => array(LogLevel::ALERT, 'message of level alert with context: {user}'),
            LogLevel::CRITICAL => array(LogLevel::CRITICAL, 'message of level critical with context: {user}'),
            LogLevel::ERROR => array(LogLevel::ERROR, 'message of level error with context: {user}'),
            LogLevel::WARNING => array(LogLevel::WARNING, 'message of level warning with context: {user}'),
            LogLevel::NOTICE => array(LogLevel::NOTICE, 'message of level notice with context: {user}'),
            LogLevel::INFO => array(LogLevel::INFO, 'message of level info with context: {user}'),
            LogLevel::DEBUG => array(LogLevel::DEBUG, 'message of level debug with context: {user}'),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsOnInvalidLevel()
    {
        $Logger = new Logger();
        $Logger->log('invalid level', 'Foo');
    }

    public function testContextReplacement()
    {
        $Logger = new Logger();
        $Logger->info('{Message {nothing} {user} {foo.bar} a}', array('user' => 'Bob', 'foo.bar' => 'Bar'));

        $expected = array('info {Message {nothing} Bob Bar a}');
        $this->assertEquals($expected, $Logger->get(false));
    }

    public function testObjectCastToString()
    {
        $dummy = $this->getMock('\stdClass', array('__toString'));
        $dummy
            ->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('DUMMY'));

        $Logger = new Logger();
        $Logger->warning($dummy);
    }

    public function testContextCanContainAnything()
    {
        $context = array(
            'bool' => true,
            'null' => null,
            'string' => 'Foo',
            'int' => 0,
            'float' => 0.5,
            'nested' => array('with object' => new \stdClass),
            'object' => new \DateTime,
            'resource' => fopen('php://memory', 'r'),
        );

        $Logger = new Logger();
        $Logger->warning('Crazy context data', $context);
    }

    public function testContextExceptionKeyCanBeExceptionOrOtherValues()
    {
        $Logger = new Logger();
        $Logger->warning('Random message', array('exception' => 'oops'));
        $Logger->critical('Uncaught Exception!', array('exception' => new \LogicException('Fail')));
    }

    public function testElapsedTime()
    {
        $Logger = new Logger();
        $this->assertNotNull($Logger->getElapsedTime());
    }

    public function testToString()
    {
        $Logger = new Logger();
        $this->assertInternalType('string', (string) $Logger);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWriteWithoutName()
    {
        $Logger = new Logger();
        $Logger->write();
    }

    public function testWriteEmpty()
    {
        $Logger = new Logger($this->file, false, true);
        $Logger->write();
        $this->assertTrue(is_file($this->file));
    }

    public function testWriteWithEntries()
    {
        $Logger = new Logger($this->file);
        $Logger->write();
        $this->assertFalse(is_file($this->file));

        $Logger->info('Something to write');
        $Logger->write();
        $this->assertTrue(is_file($this->file));
    }
}
