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

class Cache
{
	/**
	 * @var string $host
	 */
	protected $host = 'localhost';

	/**
	 * @var int $port
	 */
	protected $port = 11211;

	/**
	 * @var int $timeout
	 */
	protected $timeout = 30;

	protected \Memcache $memcache;
	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		if (isset($_ENV) && is_array($_ENV) && count($_ENV) > 0) :
			if (isset($_ENV['MEMCACHE_HOST'])) :
				$this->host = $_ENV['MEMCACHE_HOST'];
			endif;
			if (isset($_ENV['MEMCACHE_PORT'])) :
				$this->port = $_ENV['MEMCACHE_PORT'];
			endif;
			if (isset($_ENV['MEMCACHE_TIMEOUT'])) :
				$this->timeout = $_ENV['MEMCACHE_TIMEOUT'];
			endif;
		endif;
		$this->memcache = new \Memcache();
		$this->memcache->connect($this->host, $this->port, $this->timeout);
		if ($this->memcache === false) :
			throw new \Exception('Could not connect to Memcache server');
		endif;
	}

	/**
	 * Add data to the data array
	 *
	 * @param  array|string  $key
	 * @param  mixed $var
	 * @param	int $flags
	 * @param	int $expire
	 * @return bool
	 */
	public function set(string $key, mixed $value, ?int $flags = 0, ?int $expiration = 0): bool
	{
		$key = self::key($key);
		return $this->memcache->set($key, $value, $flags, $expiration);
	}

	/**
	 * Get data from the data array
	 *
	 * @param  mixed $key
	 * @param  mixed $flags
	 * @return mixed $data or false when not exists
	 */
	public function get($key, &$flags = null)
	{
		$key = self::key($key);
		return $this->memcache->get($key, $flags);
	}

	/**
	 * delete
	 *
	 * @param  mixed $key
	 * @param  mixed $timeout
	 * @return bool
	 */
	public function delete($key, $timeout = 0): bool
	{
		$key = self::key($key);
		return $this->memcache->delete($key, $timeout);
	}

	protected function key(...$value): string
	{
		$key = array();
		if (isset($_SERVER) && is_array($_SERVER) && count($_SERVER) > 0 && array_key_exists('SERVER_NAME', $_SERVER)) :
			// Add the server name to the key
			$key[] = $_SERVER['SERVER_NAME'];
		endif;

		if(count($value) > 0) :
			$key = array_merge($key, $value);
		endif;
		return implode(\DIRECTORY_SEPARATOR, $key);
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