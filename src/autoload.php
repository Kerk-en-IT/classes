<?php

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


/**
 * Require a class file for a specific PHP version
 *
 * @param  string|bool $filename
 * @return void
 */
 function require_class(string|bool $filename):void {
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
 }

/**
 * Autoloader for the Kerk en IT Framework
 *
 * @param  string $class
 * @return void
 */
spl_autoload_register(function ($class) {
	$filename = realpath(dirname(__FILE__). '/class.' . $class . '.php');
	if($filename === FALSE) :
		$filename = realpath(dirname(__FILE__) . '/class.' . strtolower($class) . '.php');
	endif;
	require_class($filename);
});

/**
 * Require specific classes
 *
 */
foreach(
	array(
		'Format',
		'DateTime'
	) as $class) :
	$filename = realpath(dirname(__FILE__) . '/class.' . $class . '.php');
	if($filename === FALSE) :
		$filename = realpath(dirname(__FILE__) . '/class.' . strtolower($class) . '.php');
	endif;
	require_class($filename);
endforeach;

/**
 * Require all classes within the src directory which arn't already required
 *
 */
/*
foreach (glob(realpath(__DIR__) . '/class.*.php') as $filename) :
	require_class($filename);
endforeach;
*/
?>