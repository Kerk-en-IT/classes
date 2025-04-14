<?php
namespace KerkEnIT;

/**
 * Memcache Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage Memcache
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2024-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.2.0
 **/

class Cache extends \Memcache
{
	/**
	 * connect
	 *
	 * @param  string $host
	 * @param  int $port
	 * @param  mixed $timeout
	 * @return bool
	 */
	public static function connect(?string $host = null, ?int $port = null, ?int $timeout = null): bool
	{
		if (!isset($_ENV) || !is_array($_ENV) || count($_ENV) == 0) :
			// Load the .env file
			$file = realpath(self::getEnvPath(dirname(__FILE__)) . '/.env');

			if ($file !== FALSE) : (
					$_ENV = parse_ini_file($file, true, INI_SCANNER_RAW));
			else :
				$_ENV = array();
			endif;
		else :
			$_ENV = array();
		endif;

		if (isset($_ENV['MEMCACHE_HOST'])) :
			$host = $_ENV['MEMCACHE_HOST'];
		endif;
		if (isset($_ENV['MEMCACHE_PORT'])) :
			$port = $_ENV['MEMCACHE_PORT'];
		endif;

		return parent::connect($host ?? 'localhost', $port, $timeout);
	}

	/**
	 * Add data to the data array
	 *
	 * @param  string $key
	 * @param  mixed $var
	 * @param  mixed $flag
	 * @param  mixed $expire
	 * @return bool
	 */
	public static function set($key, $var, $flag = null, $expire = null): bool
	{
		$key = self::key($key);
		return parent::set($key, $var, $flag = null, $expire = null);
	}

	/**
	 * Get data from the data array
	 *
	 * @param  mixed $key
	 * @param  mixed $flags
	 * @return mixed $data or false when not exists
	 */
	public static function get($key, &$flags = null)
	{
		$key = self::key($key);
		return parent::get($key, $flags);
	}

	/**
	 * delete
	 *
	 * @param  mixed $key
	 * @param  mixed $timeout
	 * @return bool
	 */
	public static function delete($key, $timeout = 0): bool
	{
		$key = self::key($key);
		return parent::delete($key, $timeout);
	}

	protected static function key(...$value): string
	{
		$key = array();
		if (isset($_SERVER) && is_array($_SERVER) && count($_SERVER) > 0 && array_key_exists('SERVER_NAME', $_SERVER)) :
			// Add the server name to the key
			$key[] = $_SERVER['SERVER_NAME'];
		endif;

		if(count($value) > 0) :
			$key = array_merge($key, $value);
		endif;
		return \implode(DIRECTORY_SEPARATOR, $key);
	}

	protected static function getEnvPath(string $dir)
	{
		$file = realpath($dir . '/.env');
		if ($file !== FALSE) :
			return $dir;
		else :
			$dir = realpath($dir . '/../');
			if ($dir !== FALSE) :
				return self::getEnvPath($dir);
			endif;
		endif;
	}

}