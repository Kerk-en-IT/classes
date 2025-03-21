<?php

namespace KerkEnIT;

use Exception;
use ErrorException;

/**
 * Log Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage Log
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2024-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.0.0
 **/
class Log
{

	private static function buffer_output(?string $color = null, ?string $param = null): string
	{
		if ($param === null) :
			return '';
		else :
			$rtn = '<div class="alert alert-' . ($color ?? 'info') . '">';
			$rtn .= $param;
			$rtn .= '</div>';
			echo $rtn;
		endif;
	}

	private static function write_output(mixed ...$params): mixed
	{
		\ob_flush();
		if (\is_array($params)) :
			if (count($params) == 0) :
				return null;
			elseif (count($params) == 1) :
				$params = $params[0];
				if (is_array($params) && array_key_exists(0, $params)) :
					return self::write_output($params[0]);
				endif;
			else:
				return \implode(PHP_EOL, $params);
			endif;
		endif;
		if (\is_string($params)) :
			return $params;
		elseif (\is_numeric($params)) :
			return $params;
		elseif (\is_bool($params)) :
			return $params;
		elseif (\is_object($params)) :
			return \json_encode($params);
		elseif (\is_array($params)) :
			return \implode(PHP_EOL, $params);
		else :
			throw new Exception('Invalid parameter type: ' . gettype($params), 500);
		endif;
	}

	public static function error_handler(?string $color = null, ...$params): void
	{
		if (php_sapi_name() == "cli") :
			\ob_start();
		endif;
		$message = self::write_output($params);

		if (php_sapi_name() == "cli") :
			if ($color === null) :
				$color = "\033[31m";
			else :
				switch ($color):
					case 'info':
						$color = "\033[32m";
						break;
					case 'warning':
						$color = "\033[33m";
						break;
					case 'danger':
						$color = "\033[31m";
						break;
					case 'notice':
						$color = "\033[34m";
						break;
					default:
						$color = "\033[32m";
						break;
				endswitch;
			endif;
			echo $color . $message . "\033[0m\n";
		elseif (defined('DEBUG') && DEBUG) :
			if (ini_get('display_errors') == 0 && defined('DOWNLOAD') && \DOWNLOAD) :
				echo '';
			else :
				echo '<pre>';
				self::buffer_output($color, \str_replace(\PHP_EOL, '<br>', $message));
				echo '</pre>';
			endif;
		endif;
		if (PHP_OS != 'Darwin' && getenv('ZSH') !== true):
			error_log($message);
		endif;
	}

	public static function log(...$params)
	{
		self::error_handler('notice', $params);
	}

	public static function info(...$params)
	{
		self::error_handler('info', $params);
	}

	public static function warn(...$params)
	{
		self::error_handler('warning', $params);
	}

	public static function error(...$params)
	{
		self::error_handler('danger', $params);
		if (defined('DEBUG') && DEBUG) :
			die();
		endif;
	}

	public static function error_message(string|null &$message)
	{
		global $mysqli;
		global $sql;

		$error = error_get_last();
		if ($error != null) :
			// Fatal error, E_ERROR === 1
			//if ($error['type'] === E_ERROR | E_USER_ERROR) :
			$message .= PHP_EOL . '> ' . $error['message'];
		//endif;
		endif;

		$stack = array_reverse(debug_backtrace());
		array_pop($stack);
		if (is_array($stack) && count($stack) > 0) :
			foreach ($stack as $index => $trace) :
				if (array_key_exists('file', $trace) && !str_contains($trace['file'], '/class.ErrorHandeling.php')) :
					$message .= PHP_EOL . ($index + 1) . '. File: ' . $trace['file'];
				endif;
				foreach ($trace as $key => $value) :
					if ($key === 'file' && str_contains($value, '/class.ErrorHandeling.php')) :
						continue 2;
					else :
						if ($key !== 'file') :
							if($key == 'function' && $value == 'shutdown_function') :
								continue 2;
							endif;
							if (is_array($value)) :
								$message .= PHP_EOL . implode(PHP_EOL . '     - ', $value);
							else :
								$message .= PHP_EOL . '   - ' . ucfirst($key) . ': ' . json_encode($value);
							endif;
						endif;
					endif;
				endforeach;
			endforeach;
		endif;

		if (!empty($mysqli->error)) {
			$message .= $mysqli->error;
		}

		if (isset($sql) && !empty($sql)) :
			$message .= \PHP_EOL .  \PHP_EOL .  '```sql' . \PHP_EOL .  preg_replace('/\s+/', ' ', $sql) . \PHP_EOL . '```';
		endif;
	}
}


if (!function_exists('varDump')) :
	/**
	 * var_dump all content
	 *
	 * @param  mixed $params
	 * @return void
	 */
	function varDump(...$params)
	{
		Log::log($params);
	}
endif;

if (!function_exists('varDie')) :
	function varDie(...$params)
	{
		Log::error($params);
		if (defined('DEBUG') && DEBUG) :
			die();
		endif;
	}
endif;

if (!function_exists('log')) :
	/**
	 * var_dump all content
	 *
	 * @param  mixed $params
	 * @return void
	 */
	function log(...$params)
	{
		varDump($params);
	}
endif;

if (!function_exists('error')) :
	function error(...$params)
	{
		varDie($params);
	}
endif;
?>