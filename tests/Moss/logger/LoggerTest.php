<?php
namespace moss\logger;


class LoggerTest extends \PHPUnit_Framework_TestCase {

	protected $file = 'test_log.txt';

	public function setUp() {
		if(is_file($this->file)) {
			unlink($this->file);
		}
	}

	public function tearDown() {
		if(is_file($this->file)) {
			unlink($this->file);
		}
	}

	public function testLogging() {
		$Logger = new Logger();

		$Logger->debug('debug');
		$Logger->info('info');
		$Logger->notice('notice');
		$Logger->warning('warning');
		$Logger->error('error');
		$Logger->critical('critical');
		$Logger->alert('alert');
		$Logger->emergency('emergency');

		$result = array(
			array('level' => 100, 'message' => 'debug', 'context' => array()),
			array('level' => 200, 'message' => 'info', 'context' => array()),
			array('level' => 250, 'message' => 'notice', 'context' => array()),
			array('level' => 300, 'message' => 'warning', 'context' => array()),
			array('level' => 400, 'message' => 'error', 'context' => array()),
			array('level' => 500, 'message' => 'critical', 'context' => array()),
			array('level' => 550, 'message' => 'alert', 'context' => array()),
			array('level' => 600, 'message' => 'emergency', 'context' => array())
		);

		$this->assertEquals($result, $Logger->get(false));
	}

	public function testElapsedTime() {
		$Logger = new Logger();
		$this->assertNotNull($Logger->getElapsedTime());
	}

	public function testToString() {
		$Logger = new Logger();
		$this->assertInternalType('string', (string) $Logger);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testWriteWithoutName() {
		$Logger = new Logger();
		$Logger->write();
	}

	public function testWriteEmpty() {
		$Logger = new Logger($this->file, false, true);
		$Logger->write();
		$this->assertTrue(is_file($this->file));
	}

	public function testWriteWithEntries() {
		$Logger = new Logger($this->file);
		$Logger->write();
		$this->assertFalse(is_file($this->file));

		$Logger->info('Something to write');
		$Logger->write();
		$this->assertTrue(is_file($this->file));
	}
}
