<?php
namespace moss\logger;

use moss\logger\LoggerInterface;

/**
 * Logger implementation
 *
 * @package Moss Logger
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Logger implements LoggerInterface {

	protected $log = array();
	protected $start;
	protected $path;
	protected $level;

	protected $overwrite;
	protected $writeEmpty;

	protected $levels = array(
		100 => 'DEBUG',
		200 => 'INFO',
		250 => 'NOTICE',
		300 => 'WARNING',
		400 => 'ERROR',
		500 => 'CRITICAL',
		550 => 'ALERT',
		600 => 'EMERGENCY',
	);

	/**
	 * Constructor
	 *
	 * @param null|string $path       path to log file
	 * @param bool        $overwrite  if true - will overwrite log file
	 * @param bool        $writeEmpty if true will write log whether there are messages or not
	 * @param int         $level      all logs with lower level will be ignored
	 */
	public function __construct($path = null, $overwrite = true, $writeEmpty = false, $level = 0) {
		$this->path = str_replace('\\', '/', $path);
		$this->start = microtime(true);
		$this->overwrite = (bool) $overwrite;
		$this->writeEmpty = (bool) $writeEmpty;
		$this->level = (int) $level;
	}

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function emergency($message, array $context = array()) {
		return $this->log(600, $message, $context);
	}

	/**
	 * Action must be taken immediately.
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function alert($message, array $context = array()) {
		return $this->log(550, $message, $context);
	}

	/**
	 * Critical conditions.
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function critical($message, array $context = array()) {
		return $this->log(500, $message, $context);
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function error($message, array $context = array()) {
		return $this->log(400, $message, $context);
	}

	/**
	 * Exceptional occurrences that are not errors.
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function warning($message, array $context = array()) {
		return $this->log(300, $message, $context);
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function notice($message, array $context = array()) {
		return $this->log(250, $message, $context);
	}

	/**
	 * Interesting events.
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function info($message, array $context = array()) {
		return $this->log(200, $message, $context);
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function debug($message, array $context = array()) {
		return $this->log(100, $message, $context);
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return Logger
	 */
	public function log($level, $message, array $context = array()) {
		if($level < $this->level) {
			return $this;
		}

		$this->log[] = array(
			'timestamp' => microtime(true),
			'timestampDelta' => 0,
			'memory' => memory_get_usage(true),
			'memoryDelta' => 0,
			'level' => $level,
			'context' => $context,
			'message' => $message
		);

		$this->calculateDelta();

		return $this;
	}

	/**
	 * Return all logged data
	 *
	 * @param bool $verbose
	 *
	 * @return array
	 */
	public function get($verbose = true) {
		if(!$verbose) {
			$log = array();

			foreach($this->log as $entry) {
				$log[] = array(
					'level' => $entry['level'],
					'message' => $entry['message'],
					'context' => $entry['context']
				);
			}

			return $log;
		}

		return $this->log;
	}

	/**
	 * Calculates delta for time and memory
	 */
	protected function calculateDelta() {
		$current = end($this->log);
		$key = key($this->log);
		$last = prev($this->log);

		if(!$current || !$last) {
			return;
		}

		$this->log[$key]['timestampDelta'] = round($current['timestamp'] - $last['timestamp'], 4);
		$this->log[$key]['memoryDelta'] = round($current['memory'] - $last['memory'], 4);
	}

	/**
	 * Calculates time from the creation Log instance
	 *
	 * @return string
	 */
	public function getElapsedTime() {
		return number_format(microtime(true) - $this->start, 4);
	}

	/**
	 * Casts log to string
	 *
	 * @return string
	 */
	public function __toString() {
		ob_start();

		foreach($this->log as $entry) {
			echo sprintf(
				"%s (%s) - Time: %s\tMemory: %s\n%s\n%s\n\n",
				$entry['level'],
				$this->levels[$entry['level']],
				date('Y-m-d H:i:s', $entry['timestamp']) . ':' . str_pad(substr($entry['timestamp'], strpos($entry['timestamp'], '.') + 1), 4, 0, STR_PAD_RIGHT),
				number_format($entry['memory'], 0, '.', ' '),
				$entry['message'],
				print_r($entry['context'], 1)
			);
		}

		echo "Time: ", $this->getElapsedTime(), "\nMemory: ", number_format(memory_get_peak_usage(true), 0, '.', ' ');

		return ob_get_clean();
	}

	/**
	 * Saves object state
	 */
	public function write() {
		if(!$this->path) {
			throw new \InvalidArgumentException('Can not write log without path');
		}

		if(!$this->writeEmpty && !count($this->log)) {
			return;
		}

		$path = substr($this->path, 0, strrpos($this->path, '/'));
		if(!empty($path) && !is_dir($path)) {
			mkdir($path, 0777, true);
		}

		file_put_contents(
			$this->path,
			(is_file($this->path) && !$this->overwrite ? "\n\n" : null) . (string) $this,
			$this->overwrite ? null : FILE_APPEND
		);
	}
}
