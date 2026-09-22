<?php

namespace KerkEnIT;

/**
 * Autoloader for the Kerk en IT Framework
 *
 * PHP versions 8.0 or higher (str_contains, spl_autoload_register).
 * Recommended PHP 8.4+ (class.Video.php requires 8.4 property accessors).
 *
 * @version    1.2.0
 * @package    KerkEnIT
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2025-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.0.0
 **/

if (!function_exists('getEnvPath')) :
	function getEnvPath(string $dir, int $depth = 0)
	{
		$file = realpath($dir . '/.env');
		if ($file !== FALSE) :
			return $dir;
		else :
			$parent = realpath($dir . '/../');
			if(trim(basename($dir), '/') === 'home' || trim(basename($dir), '/') === '') :
				return FALSE;
			endif;
			// Stop at the filesystem root: realpath('/..') returns '/' (not FALSE),
			// which would otherwise cause infinite recursion.
			if ($parent !== FALSE && $parent !== $dir && $depth < 10) :
				return getEnvPath($parent, $depth + 1);
			endif;
		endif;
		return FALSE;
	}
endif;
// Load the environment variables
if (!isset($_ENV) || !is_array($_ENV) || count($_ENV) == 0) :
	$file = realpath(getEnvPath(dirname(__FILE__)) . '/.env');
	if ($file !== FALSE) :
		$_ENV = parse_ini_file($file, true, INI_SCANNER_RAW);
	endif;
endif;
// Load the environment variables for the CLI
if ((!isset($_ENV) || !is_array($_ENV) || count($_ENV) == 0)) :
	$file = realpath(getEnvPath(dirname(__FILE__)) . '/.env');
	if ($file !== FALSE) :
		$env = explode(PHP_EOL, file_get_contents($file));
		foreach ($env as $line) :
			$line = explode('=', $line);
			if (count($line) == 2) :
				$value = trim($line[1], '"');
				putenv(trim($line[0], '"') . "=" . $value);
				$_ENV[trim($line[0], '"')] = $value;
			endif;
		endforeach;
	endif;
endif;
foreach ($_ENV as $key => $value) :
	if ($value === 'true') :
		$_ENV[$key] = true;
	elseif ($value === 'false') :
		$_ENV[$key] = false;
	endif;
endforeach;

extract($_ENV);
define('PHP_MAJOR_MINOR_VERSION', (int)str_replace(".", "", substr(PHP_VERSION, 0, 3)));

// Get debug hosts from the environment
if (isset($_ENV['debug_hosts']) && \array_key_exists('REMOTE_ADDR', $_SERVER) && in_array($_SERVER['REMOTE_ADDR'], array_map('getHostByName', explode(',', $_ENV['debug_hosts'])))) :
	if (!defined('DEBUG')) :
		define('DEBUG', true);
	endif;
endif;

if (!defined('ABSPATH')) :
	if (PHP_MAJOR_MINOR_VERSION >= 83) :
		foreach(array('class.Log.php', 'global.php') as $file) :
			$filename = realpath(dirname(__FILE__) . '/' . $file);
			if ($filename !== FALSE) :
				require_once($filename);
			endif;
		endforeach;
	endif;
endif;
/**
 * Autoloader for the Kerk en IT Framework
 *
 * @param	string $class
 * @return void
 */
spl_autoload_register(function ($class) {
	$class = str_replace(__NAMESPACE__ . '\\', '', $class);
	$filename = realpath(dirname(__FILE__) . '/class.' . $class . '.php');
	if ($filename === FALSE) :
		$filename = realpath(dirname(__FILE__) . '/class.' . strtolower($class) . '.php');
	endif;
	if ($filename !== FALSE) :
		if (PHP_MAJOR_MINOR_VERSION >= 84) :
			// For PHP 8.4 and higher, include the file directly
			require_once($filename);
		elseif (PHP_MAJOR_MINOR_VERSION < 80) :
			// Skip files that require PHP 8.x for versions below 8.0
			if (str_contains(file_get_contents($filename), 'PHP versions 8.')) :
				// Skip this file for PHP versions below 8.x
				throw new \Exception('Skipping file ' . $filename . ' for PHP versions below 8.x');
			else :
				require_once($filename);
			endif;
		elseif (PHP_MAJOR_MINOR_VERSION <= 83) :
			// Skip files that require PHP 8.5 or 8.4 for versions <= 8.3
			if (str_contains(file_get_contents($filename), 'PHP versions 8.5')) :
				throw new \Exception('Skipping file ' . $filename . ' for PHP version 8.3 or lower. Requires at least PHP 8.5');
			elseif (str_contains(file_get_contents($filename), 'PHP versions 8.4')) :
				throw new \Exception('Skipping file ' . $filename . ' for PHP version 8.3 or lower. Requires at least PHP 8.4');
			else :
				require_once($filename);
			endif;
		else :
			// For PHP 8.4 and higher, include the file directly (fallback)
			require_once($filename);
		endif;
	endif;
});