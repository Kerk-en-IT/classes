<?php
namespace KerkEnIT;

	/**
	 * Console
	 */
	class Console
	{
		private static $formatter = null;
		private static float|null $total_time_start = null;
		private static float $time_start = 0.00;
		private static $index = 0;
		private static $count = null;

		/**
		 * init
		 *
		 * @param  int|float $count
		 * @return void
		 */
		public static function init($count)
		{
			self::$index = 0;
			self::$count = $count;
		}
		/**
		 * log
		 *
		 * @param  array $params
		 * @return void
		 */
		public static function log(...$params)
		{
			if (!is_array($params)) :
				$params = array($params);
			endif;
			foreach ($params as $param) :
				$message = self::WriteLine($param);
				self::PrintLine($message);
			endforeach;
		}

		/**
		 * info
		 *
		 * @param  mixed $param
		 * @param  int|float $count
		 * @param  int $index
		 * @return void
		 */
		public static function info($param = null, $count = null, $index = null)
		{
			if ($count !== null && self::$count !== NULL && is_float($count)) :
				if (!empty($param)) :
					$message = self::WriteLine($param, self::GetPercentage($count / 100));
					self::PrintLine($message);
				endif;
				return;
			elseif ($count !== null && self::$count === NULL) :
				self::$count = $count;
			elseif (self::$count !== NULL) :
				$count = self::$count;
			else :
				return;
			endif;
			if ($index === null) :
				$index = self::$index;
				self::$index++;
			endif;
			if (!empty($param)) :
				$message = self::WriteLine($param, self::GetProgress($index, $count));
				self::PrintLine($message);
			endif;
		}

		/**
		 * error
		 *
		 * @param  array $params
		 * @return void
		 */
		public static function error(...$params)
		{
			if (!is_array($params)) :
				$params = array($params);
			endif;
			foreach ($params as $param) :
				$message = self::WriteLine($param);
				if (!empty($message)) :
					error_log($message);
				endif;
				self::PrintLine($message);
			endforeach;
			if (defined('DEBUG') && DEBUG) :
				die();
			else :
				self::PrintLine('henk');
			endif;
		}

		/**
		 * GetPercentage
		 *
		 * @param  float $x
		 * @param  int $decimals
		 * @return string
		 */
		private static function GetPercentage($x, $decimals = 2)
		{
			if (self::$formatter == null) :
				self::$formatter = new \NumberFormatter('en_US', \NumberFormatter::PERCENT);
				numfmt_set_attribute(self::$formatter, \NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
			endif;
			if (self::$formatter !== null) :

				//varDump(self::$formatter->format($x));
				return self::$formatter->format($x);
			else :
				return sprintf("%." . $decimals . "f%%", $x * 100);
			endif;
		}

		/**
		 * GetProgress
		 *
		 * @param  float|int $index
		 * @param  float|int $count
		 * @param  int $decimals
		 * @return string
		 */
		private static function GetProgress($index, $count, $decimals = 2)
		{
			if ($index != 0 && $count != 0) :
				$x = $index / $count;
			else :
				$x = 0;
			endif;
			return self::GetPercentage($x, $decimals);
		}

		/**
		 * PrintLine
		 *
		 * @param  string $param
		 * @return void
		 */
		private static function PrintLine(string $param)
		{
			if (!empty($param)) :
				error_log($param);
			endif;
		}

		/**
		 * WriteLine
		 *
		 * @param  mixed $first
		 * @param  mixed|null $second
		 * @return string
		 */
		private static function WriteLine($first, $second = null)
		{
			$time_end = floatval(microtime(true));
			$execution_time = ($time_end - self::$time_start);
			if (self::$total_time_start === null) :
				self::$total_time_start = microtime(true);
			endif;
			$execution_time = ($time_end - self::$time_start);
			$total_execution_time = ($time_end - self::$total_time_start);
			self::$time_start = $time_end;
			return date("H:i:s") . " (" . number_format($execution_time, 2) . "s / " . gmdate("H:i:s", $total_execution_time) . "s) - " . self::GetLine($first) . (!empty($second) ? " - " . $second : "");
		}

		/**
		 * GetLine
		 *
		 * @param  mixed $param
		 * @return void
		 */
		private static function GetLine($param)
		{
			if (is_array($param)) :
				return (json_encode($param));
			elseif (is_object($param)) :
				return (json_encode($param));
			elseif (defined('DEBUG') && !DEBUG) :
				global $data_path;
				global $dist_path;
				return (str_replace(array($dist_path, $data_path), '', $param));
			elseif (realpath($param)) :
				global $data_path;
				global $dist_path;
				return (str_replace(array($dist_path, $data_path), '', $param));
			else :
				return $param;
			endif;
		}
	}
?>