<?php
namespace KerkEnIT;
/**
 * Autoloader for the Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @version    1.2.0
 * @package    KerkEnIT
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2025-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.0.0
 **/

 // Load the environment variables
if(!isset($_ENV) || !is_array($_ENV) || !count($_ENV) == 0) :
	$_ENV = parse_ini_file('.env');
endif;
// Load the environment variables for the CLI
if (php_sapi_name() == 'cli' && (!isset($_ENV) || !is_array($_ENV) || !count($_ENV) == 0)) :
	$file = realpath($_SERVER["DOCUMENT_ROOT"] . '/.env');
	if ($file !== false) :
		$env = explode(PHP_EOL, file_get_contents($file));
		foreach ($env as $line) :
			$line = explode('=', $line);
			if (count($line) == 2) :
				putenv(trim($line[0], '"') . "=" . trim($line[1], '"'));
				$_ENV[trim($line[0], '"')] = trim($line[1], '"');
			endif;
		endforeach;
	endif;
endif;

// Get debug hosts from the environment
if(isset($_ENV['debug_hosts']) && in_array($_SERVER['REMOTE_ADDR'], array_map('getHostByName', explode(',', $_ENV['debug_hosts'])))) :
	if(!defined('DEBUG')) :
		define('DEBUG', true);
	endif;
endif;


/**
 * Autoloader for the Kerk en IT Framework
 *
 * @param  string $class
 * @return void
 */
spl_autoload_register(function ($class) {
	$class = str_replace(__NAMESPACE__ . '\\', '', $class);
	$filename = realpath(dirname(__FILE__) . '/class.' . $class . '.php');
	if ($filename === FALSE) :
		$filename = realpath(dirname(__FILE__) . '/class.' . strtolower($class) . '.php');
	endif;
	if ($filename !== FALSE) :
		if (substr(PHP_VERSION, 0, 3) === '8.4') :
			require_once($filename);
		elseif (substr(PHP_VERSION, 0, 3) === '8.3') :
			if (!str_contains(file_get_contents($filename), 'PHP versions 8.4')) :
				require_once($filename);
			elseif (!str_contains(file_get_contents($filename), 'PHP versions')) :
				require_once($filename);
			endif;;
		else :
			require_once($filename);
		endif;
	endif;
});


/**
 * Require all classes within the src directory which arn't already required
 * @deprecated deprecated since version 1.2.0. Will be removed in version 1.3.0
 * @since      Method available since Release 1.1.0
 */
foreach (glob(realpath(__DIR__) . '/class.*.php') as $filename) :
	$filename = pathinfo($filename, PATHINFO_FILENAME);
	$class = str_replace('class.', '', $filename);
	if (!class_exists('\\' . __NAMESPACE__ . '\\' . $class)) :
		$filename = realpath(dirname(__FILE__) . '/class.' . $class . '.php');
		if ($filename === FALSE) :
			$filename = realpath(dirname(__FILE__) . '/class.' . strtolower($class) . '.php');
		endif;
		if ($filename !== FALSE) :
			if (substr(PHP_VERSION, 0, 3) === '8.4') :
				require_once($filename);
			elseif (substr(PHP_VERSION, 0, 3) === '8.3') :
				if (!str_contains(file_get_contents($filename), 'PHP versions 8.4')) :
					require_once($filename);
				elseif (!str_contains(file_get_contents($filename), 'PHP versions')) :
					require_once($filename);
				endif;;
			else :
				require_once($filename);
			endif;
		endif;
	endif;
endforeach;
?>