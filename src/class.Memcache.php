<?php
if (!class_exists('\Memcache')) :
	if(!defined('MEMCACHE_COMPRESSED')) :
		define('MEMCACHE_COMPRESSED', 2);
	endif;
	/**
	 * MemcacheDummy clone of \Memcache
	 */
	class Memcache
	{
		// Properties
		private static $host;
		private static $port;
		private static $data = array();

		/**
		 * connect
		 *
		 * @param	string $host
		 * @param	int $port
		 * @param  mixed $timeout
		 * @return bool
		 */
		public static function connect(string $host, int $port = null, int $timeout = null): bool
		{
			self::$host = $host;
			self::$port = $port;
			return true;
		}


		/**
		 * Add data to the data array
		 *
		 * @param	string $key
		 * @param  mixed $var
		 * @param  mixed $flag
		 * @param  mixed $expire
		 * @return bool
		 */
		public static function set($key, $var, $flag = null, $expire = null): bool
		{
			self::$data[$key] = $var;
			return true;
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
			if (array_key_exists($key, self::$data)) :
				return self::$data[$key];
			else :
				return false;
			endif;
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
			if (array_key_exists($key, self::$data)) :
				unset(self::$data[$key]);
			endif;
			return true;
		}
	}

endif;
?>