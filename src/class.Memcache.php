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
		private static string $host;
		private static int $port;
		private static array $data = array();

		/**
		 * connect
		 *
		 * @param	string $host
		 * @param	int $port
		 * @param 	mixed $timeout
		 * @return	bool
		 */
		public static function connect(string $host, ?int $port = null, ?int $timeout = null): bool
		{
			self::$host = $host;
			self::$port = $port;
			return true;
		}


		/**
		 * Add data to the data array
		 *
		 * @param	string $key The key that will be associated with the item.
		 * @param	mixed $var The variable to store. Strings and integers are stored as is, other types are stored serialized.
		 * @param	int $flag Use MEMCACHE_COMPRESSED to store the item compressed (uses zlib).
		 * @param	int $expire Expiration time of the item. If it's equal to zero, the item will never expire. You can also use Unix timestamp or a number of seconds starting from current time, but in the latter case the number of seconds may not exceed 2592000 (30 days).
		 * @return	bool
		 */
		public static function set(string $key, $var, ?int $flag = null, ?int $expire = null): bool
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