<?php
namespace moss\logger;

use Psr\Log\AbstractLogger;

/**
 * PSR-3 Logger implementation
 *
 * @package Moss Logger
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Logger extends AbstractLogger {

	const EMERGENCY = 'emergency';
	const ALERT = 'alert';
	const CRITICAL = 'critical';
	const ERROR = 'error';
	const WARNING = 'warning';
	const NOTICE = 'notice';
	const INFO = 'info';
	const DEBUG = 'debug';

	protected $log = array();
	protected $start;
	protected $path;
	protected $ignoredLevels;

	protected $overwrite;
	protected $writeEmpty;

	/**
	 * Constructor
	 *
	 * @param null|string $path       path to log file
	 * @param bool        $overwrite  if true - will overwrite log file
	 * @param bool        $writeEmpty if true will write log whether there are messages or not
	 * @param int         $level      all logs with lower level will be ignored
	 */
	public function __construct($path = null, $overwrite = true, $writeEmpty = false, $ignoredLevels = array()) {
		$this->path = str_replace('\\', '/', $path);
		$this->start = microtime(true);
		$this->overwrite = (bool) $overwrite;
		$this->writeEmpty = (bool) $writeEmpty;
		$this->ignoredLevels = $ignoredLevels;
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
		switch($level) {
			case self::EMERGENCY:
			case self::ALERT:
			case self::CRITICAL:
			case self::ERROR:
			case self::WARNING:
			case self::NOTICE:
			case self::INFO:
			case self::DEBUG:
				break;
			case in_array($level, $this->ignoredLevels):
				return $this;
			default:
				throw new \InvalidArgumentException(sprintf('Invalid level submited "%s"', (string) $level));
		}

		$this->log[] = array(
			'timestamp' => microtime(true),
			'timestampDelta' => 0,
			'memory' => memory_get_usage(true),
			'memoryDelta' => 0,
			'level' => $level,
			'context' => $context,
			'message' => (string) $message
		);

		$this->calculateDelta();

		return $this;
	}

	/**
	 * Interpolates context values into the message placeholders.
	 */
	protected function interpolate($message, array $context = array()) {
		$replace = array();
		foreach($context as $key => $val) {
			$replace['{' . $key . '}'] = $val;
		}

		return strtr($message, $replace);
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
				$log[] = sprintf('%s %s', $entry['level'], $this->interpolate($entry['message'], $entry['context']));
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
