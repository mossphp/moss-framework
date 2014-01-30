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
        $logger = new Logger();
        $logger->{$level}($message, array('user' => 'Bob'));
        $logger->log($level, $message, array('user' => 'Bob'));

        $expected = array(
            $level . ' message of level ' . $level . ' with context: Bob',
            $level . ' message of level ' . $level . ' with context: Bob',
        );

        $this->assertEquals($expected, $logger->get(false));
    }

    /**
     * @dataProvider provideLevelsAndMessages
     */
    public function testIgnoredLogsAtAllLevels($level, $message)
    {
        $levels = array(LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG);
        $logger = new Logger(null, true, false, $levels);
        $logger->{$level}($message, array('user' => 'Bob'));
        $logger->log($level, $message, array('user' => 'Bob'));

        $this->assertEmpty($logger->get(false));
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
     * @expectedExceptionMessage Invalid level submitted "invalid level"
     */
    public function testThrowsOnInvalidLevel()
    {
        $logger = new Logger();
        $logger->log('invalid level', 'Foo');
    }

    public function testContextReplacement()
    {
        $logger = new Logger();
        $logger->info('{Message {nothing} {user} {foo.bar} a}', array('user' => 'Bob', 'foo.bar' => 'Bar'));

        $expected = array('info {Message {nothing} Bob Bar a}');
        $this->assertEquals($expected, $logger->get(false));
    }

    public function testObjectCastToString()
    {
        $dummy = $this->getMock('\stdClass', array('__toString'));
        $dummy
            ->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('DUMMY'));

        $logger = new Logger();
        $logger->warning($dummy);
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

        $logger = new Logger();
        $logger->warning('Crazy context data', $context);
    }

    public function testContextExceptionKeyCanBeExceptionOrOtherValues()
    {
        $logger = new Logger();
        $logger->warning('Random message', array('exception' => 'oops'));
        $logger->critical('Uncaught Exception!', array('exception' => new \LogicException('Fail')));
    }

    public function testElapsedTime()
    {
        $logger = new Logger();
        $this->assertNotNull($logger->getElapsedTime());
    }

    public function testToString()
    {
        $logger = new Logger();
        $this->assertInternalType('string', (string) $logger);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable write log without path
     */
    public function testWriteWithoutName()
    {
        $logger = new Logger();
        $logger->write();
    }

    public function testWriteEmpty()
    {
        $logger = new Logger($this->file, false, true);
        $logger->write();
        $this->assertTrue(is_file($this->file));
    }

    public function testWriteWithEntries()
    {
        $logger = new Logger($this->file);
        $logger->write();
        $this->assertFalse(is_file($this->file));

        $logger->info('Something to write');
        $logger->write();
        $this->assertTrue(is_file($this->file));
    }
}
