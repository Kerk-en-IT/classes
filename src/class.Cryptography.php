<?php
namespace KerkEnIT;
use Exception;

/**
 * Cryptography Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage Cryptography
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2025-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.1.0
 **/
class Cryptography
{
	/**
	 * Encrypt a string
	 *
	 * @deprecated This function has been DEPRECATED as of PHP 7.1.0 and REMOVED as of PHP 7.2.0. Relying on this function is highly discouraged.
	 *
	 * @param  string $string The data to be encrypted.
	 * @return string Encrypted string
	 */
	public static function encrypt($string)
	{
		if (function_exists('mcrypt_create_iv') && function_exists('mcrypt_get_iv_size') && function_exists('mcrypt_encrypt')) :
			$iv = \mcrypt_create_iv(
				\mcrypt_get_iv_size(\MCRYPT_RIJNDAEL_128, \MCRYPT_MODE_CBC),
				\MCRYPT_DEV_URANDOM
			);

			$encrypted = base64_encode(
				$iv .
					\mcrypt_encrypt(
						\MCRYPT_RIJNDAEL_128,
						hash('sha256', getenv('ENCKEY'), true),
						$string,
						\MCRYPT_MODE_CBC,
						$iv
					)
			);

			return $encrypted;
		else :
			return $string;
		endif;
	}

	/**
	 * Decrypt a string
	 *
	 * @deprecated This function has been DEPRECATED as of PHP 7.1.0 and REMOVED as of PHP 7.2.0. Relying on this function is highly discouraged.
	 *
	 * @param  encrypted $string The data to be decrypted.
	 * @return string Decrypted string
	 */
	public static function decrypt($encrypted)
	{
		$data = base64_decode($encrypted);
		$iv = substr($data, 0, \mcrypt_get_iv_size(\MCRYPT_RIJNDAEL_128, \MCRYPT_MODE_CBC));

		$decrypted = rtrim(
			\mcrypt_decrypt(
				\MCRYPT_RIJNDAEL_128,
				hash('sha256', getenv('ENCKEY'), true),
				substr($data, \mcrypt_get_iv_size(\MCRYPT_RIJNDAEL_128, \MCRYPT_MODE_CBC)),
				\MCRYPT_MODE_CBC,
				$iv
			),
			"\0"
		);

		return $decrypted;
	}

	private static $hashesFiles = array();

	public static function getGitFileVersion(string|null $file): array|int|string
	{
		if ($file !== null) :
			if (!empty($file)) :
				global $dist_path;
				$filePath = realpath($file);

				if (!$filePath) :
					$filePath = realpath($dist_path . ltrim(ltrim($file, '.'), '/'));
				endif;
				try {
					if ($filePath !== false && !array_key_exists($filePath, self::$hashesFiles)) :
						$sha265 = trim(shell_exec("git log -p '$filePath' | awk '/oid sha256:/ {print $2}' | awk '{print substr($0, 8, 64)}'"));
						if (strlen($sha265) === 64) :
							self::$hashesFiles[$filePath] = substr($sha265, 8, 8);
						endif;
					endif;
				} catch (Exception $e) {
					throw $e;
					die();
				}

				if ($filePath !== false && array_key_exists($filePath, self::$hashesFiles)) :
					return self::$hashesFiles[$filePath];
				endif;
				$mtime = time();
				$ctime = time();
				global $first_blog_time;

				if ($filePath !== false) :
					$mtime = filemtime($filePath);
					$ctime = filectime($filePath);
				endif;
			endif;
			$ctime -= $first_blog_time;
			$mtime -= $first_blog_time;

			$ctime = log10(abs($ctime));
			$mtime = log10(abs($mtime));
			if ($filePath !== false && str_ends_with($filePath, '.css') && preg_match('~[0-9]+~', md5_file($filePath))) :
				self::$hashesFiles[$filePath] = preg_replace("/[^0-9]/", '', md5_file($filePath)) % 9998;;
				return self::$hashesFiles[$filePath];
			endif;
			if ($filePath !== false && $mtime - $ctime > 5) :
				self::$hashesFiles[$filePath] = $mtime - $ctime;
				return self::$hashesFiles[$filePath];
			endif;
			if ($filePath !== false && ((floatval($mtime) % 3.77) + (floatval($ctime) % 6.99) / 0.189) > 10.0) :
				self::$hashesFiles[$filePath] = intval(log10(abs(intval(log10(abs($mtime)) % 3.77) + intval(log10(abs($ctime)) % 6.99)) / 0.189));
				return self::$hashesFiles[$filePath];
			endif;
			//return ;
			if ($filePath !== false && preg_match('~[0-9]+~', md5_file($filePath))) :
				self::$hashesFiles[$filePath] = preg_replace("/[^0-9]/", '', md5_file($filePath)) % 9999;
				self::$hashesFiles[$filePath];
			else :
				return $mtime;
			endif;
			return self::$hashesFiles[$filePath];
		else :
			return time();
		endif;
	}

	public static function getFileVersion(string|null  $file)
	{
		$mtime = time();
		$ctime = time();
		global $first_blog_time;
		if (!empty($file)) :
			global $dist_path;
			$filePath = realpath($dist_path . ltrim(ltrim($file, '.'), '/'));

			if ($filePath !== false) :
				$mtime = filemtime($filePath);
				$ctime = filectime($filePath);
			endif;
		endif;
		$ctime -= $first_blog_time;
		$mtime -= $first_blog_time;
		if ($filePath !== false && str_ends_with($filePath, '.css') && preg_match('~[0-9]+~', md5_file($filePath))) :
			return preg_replace("/[^0-9]/", '', md5_file($filePath)) % 9999;
		endif;
		if ($mtime - $ctime > 5) :
			return $mtime - $ctime;
		endif;
		if (intval(((floatval($mtime) % 3.77) + (floatval($ctime) % 6.99) / 0.189) > 10)) :
			return intval(((floatval($mtime) % 3.77) + (floatval($ctime) % 6.99) / 0.189));
		endif;
		//return ;
		if ($filePath !== false && preg_match('~[0-9]+~', md5_file($filePath))) :
			return preg_replace("/[^0-9]/", '', md5_file($filePath)) % 9999;
		else :
			return $mtime;
		endif;
	}

	/**
	 * Get GitHub Hash
	 *
	 * @param  string $file
	 * @return string hash
	 */
	public static function get_hash(string $file): string
	{
		$hash = exec('git hash-object "' . $file . '"');
		$hash = substr($hash, 0, intval(strlen($hash) / 2));
		$hash = \Aza\Components\Math\NumeralSystem::convert($hash, 16, 62);
		$len = strlen($hash);
		$hash = substr($hash, intval($len / 6), intval($len / 3));
		return $hash;
	}

	/**
	 * Get SHA512 hash
	 *
	 * @param  string $file
	 * @return string|false SHA512 hash in base64 format
	 */
	public static function get_SHA512(string $file): string|false
	{
		return self::get_integrity($file, 'sha512');
	}

	/**
	 * Get integrity hash
	 *
	 * @param  string $file
	 * @param  string $type	[ sha256, sha384, sha512 ]
	 * @return string|false hash in base64 format or false if file not found
	 */
	public static function get_integrity(string $file, $type = 'sha384'): string|false
	{
		if (\PHP_OS == 'Darwin') :
			return false;
		endif;
		global $html_path;
		if (isset($html_path) && str_ends_with($html_path, '.shtml')) :
			return false;
		endif;

		if (!str_starts_with($file, 'https://')) :
			if (!str_starts_with($file, '/')) :
				$file = get_include_path() . '/' . $file;
			endif;
			$file = realpath($file);
			if ($file !== false) :
				$hash = shell_exec("openssl dgst -$type -binary '$file' | openssl base64 -A");
				return "$type-" . $hash;
			endif;
		else :
			$hash = shell_exec("curl -sSL '$file' | openssl dgst -$type -binary | openssl base64 -A");
			return "$type-" . $hash;
		endif;
		return false;
	}
}