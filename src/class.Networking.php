<?php

namespace KerkEnIT;

/**
 * Private class for Kerk en IT Business Logic like hour price calculation and other internal stuff
 *
 * PHP versions 8.0 or higher (union types `string|bool`, `int|float`)
 *
 * @package    Classes
 * @subpackage KerkEnIT
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2025 Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkennit.nl
 * @since      Class available since Release 1.2.1
 *
 * @requires   PHP extension "filter" (ext-filter) gethostbyname*() — filter_var()/FILTER_VALIDATE_IP
 */

class Networking
{
	/**
	 * Get the client's IP address
	 *
	 * @return string|bool The client's IP address or false if not found
	 */
	public static function get_client_ip(): string|bool
	{
		$ip = false;
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) :
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) :
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) :
			$ip = $_SERVER['HTTP_X_FORWARDED'];
		elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) :
			$ip = $_SERVER['HTTP_FORWARDED_FOR'];
		elseif (!empty($_SERVER['HTTP_FORWARDED'])) :
			$ip = $_SERVER['HTTP_FORWARDED'];
		elseif (!empty($_SERVER['REMOTE_ADDR'])) :
			$ip = $_SERVER['REMOTE_ADDR'];
		else :
			$ip = false;
		endif;

		return $ip;
	}

	/**
	 * Get the server's IP address
	 *
	 * @return string|bool The server's IP address or false if not found
	 */
	public static function get_server_ip(): string|bool
	{
		$ip = false;
		if (!empty($_SERVER['SERVER_ADDR'])) :
			$ip = $_SERVER['SERVER_ADDR'];
		endif;
		return $ip;
	}

	/**
	 * Get the IP address for a given host name
	 *
	 * @param string $host The host name to resolve
	 * @param int $type The DNS record type (DNS_A or DNS_AAAA)
	 * @return string|false The resolved IP address or false if not found
	 */
	public static function gethostbyname(string $host, int $type = DNS_A):string|false
	{
		if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) :
			return $host;
		elseif(filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) :
			return $host;
		endif;
		if ($type == DNS_A) :
			return self::gethostbyname4($host);
		elseif ($type == DNS_AAAA) :
			return self::gethostbyname6($host);
		endif;
		return false;
	}

	/**
	 * Get the IPv4 address for a given host name
	 *
	 * @param string $host The host name to resolve
	 * @return string|false The resolved IPv4 address or false if not found
	 */
	public static function gethostbyname4(string $host):string|false
	{
		$ip = false;
		if(!empty($host)) :
			if (filter_var($host, FILTER_VALIDATE_IP)) :
				$ip = $host;
			else :
				$ip = gethostbyname($host);
			endif;
			if (!empty($ip)) :
				return $ip;
			endif;
		endif;
		return false;
	}

	/**
	 * Get the IPv6 address for a given host name
	 *
	 * @param string $host The host name to resolve
	 * @param bool $try_a Whether to try IPv4 if IPv6 fails
	 * @return string|false The resolved IPv6 address or false if not found
	 */
	public static function gethostbyname6(string $host, bool $try_a = false):string|false
	{
		// get AAAA record for $host
		// if $try_a is true, if AAAA fails, it tries for A
		// the first match found is returned
		// otherwise returns false

		$dns = self::gethostbynamel6($host, $try_a);
		if ($dns == false) {
			return false;
		} else {
			return $dns[0];
		}
	}

	/**
	 * Get the list of IPv6 addresses for a given host name
	 *
	 * @param string $host The host name to resolve
	 * @param bool $try_a Whether to try IPv4 if IPv6 fails
	 * @return array|false The list of resolved IPv6 addresses or false if not found
	 */
	private static function gethostbynamel6(string $host, bool $try_a = false)
	{
		// get AAAA records for $host,
		// if $try_a is true, if AAAA fails, it tries for A
		// results are returned in an array of ips found matching type
		// otherwise returns false

		$dns6 = dns_get_record($host, DNS_AAAA);
		if ($try_a == true) {
			$dns4 = dns_get_record($host, DNS_A);
			$dns = array_merge($dns4, $dns6);
		} else {
			$dns = $dns6;
		}
		$ip6 = array();
		$ip4 = array();
		foreach ($dns as $record) {
			if ($record["type"] == "A") {
				$ip4[] = $record["ip"];
			}
			if ($record["type"] == "AAAA") {
				$ip6[] = $record["ipv6"];
			}
		}
		if (count($ip6) < 1) {
			if ($try_a == true) {
				if (count($ip4) < 1) {
					return false;
				} else {
					return $ip4;
				}
			} else {
				return false;
			}
		} else {
			return $ip6;
		}
	}

	/**
	 * Get a random user agent string.
	 *
	 * @return string
	 */
	public static function getRandomUserAgent(): string
	{
		$userAgents = [
			// Chrome (desktop)
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
			// Firefox (desktop)
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0',
			'Mozilla/5.0 (X11; Linux x86_64; rv:127.0) Gecko/20100101 Firefox/127.0',
			// Safari (desktop)
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
			// Edge (desktop)
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0',
			// Chrome (mobile)
			'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',
			'Mozilla/5.0 (Linux; Android 13; SM-S911B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36',
			// Firefox (mobile)
			'Mozilla/5.0 (Android 14; Mobile; rv:127.0) Gecko/127.0 Firefox/127.0',
			'Mozilla/5.0 (Android 13; Mobile; rv:127.0) Gecko/127.0 Firefox/127.0',
			// Safari (iPhone)
			'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/605.1.15',
			'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/20G85 Safari/605.1.15',
			// Safari (iPad)
			'Mozilla/5.0 (iPad; CPU OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/605.1.15',
			'Mozilla/5.0 (iPad; CPU OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/20G85 Safari/605.1.15',
		];
		return $userAgents[array_rand($userAgents)];
	}
}
