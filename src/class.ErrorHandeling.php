<?php

namespace KerkEnIT;

use Exception;
use ErrorException;

/**
 * ErrorHandeling Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage ErrorHandeling
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2025-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.2.0
 */

// Set the error reporting level
if (defined('DEBUG')) :
	if (DEBUG) :
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
	else :
		error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
		ini_set('display_errors', '0');
	endif;
endif;


class ErrorHandeling
{

	public static function register(): void
	{
		// Load the environment variables
		if (!isset($_ENV) || !is_array($_ENV) || !count($_ENV) == 0) :
			$file = realpath($_SERVER["DOCUMENT_ROOT"] . '/.env');
			if ($file !== FALSE) :
				$_ENV = parse_ini_file($file);
			endif;
		endif;
		// Load the environment variables for the CLI
		if ((!isset($_ENV) || !is_array($_ENV) || !count($_ENV) == 0)) :
			$file = realpath($_SERVER["DOCUMENT_ROOT"] . '/.env');
			if ($file !== FALSE) :
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

		$that = new self();
		set_error_handler(array($that, 'log_error'));
		register_shutdown_function(array($that, 'shutdown_function'));
	}

	private static function Project(): string
	{
		if(getenv('PROJECT_NAME') !== false) :
			return getenv('PROJECT_NAME');
		elseif (!array_key_exists('PROJECT_NAME', $_ENV) || $_ENV['PROJECT_NAME'] === false) :
			putenv('PROJECT_NAME="My Fantastic App"');
		endif;

		if (getenv('PROJECT_NAME') !== false) :
			return getenv('PROJECT_NAME');
		elseif (array_key_exists('PROJECT_NAME', $_ENV)) :
			return $_ENV['PROJECT_NAME'];
		endif;

		return '';
	}

	private static function ProjectDomain(): string
	{
		if (getenv('PROJECT_DOMAIN') !== false) :
			return getenv('PROJECT_DOMAIN');
		elseif (!array_key_exists('PROJECT_DOMAIN', $_ENV) || $_ENV['PROJECT_DOMAIN'] === false) :
			if (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) :
				$domain = explode('.', $_SERVER['SERVER_NAME']);
				if (count($domain) === 3) :
					putenv('PROJECT_DOMAIN="' . $domain[1] . '.' . $domain[2] . '"');
				else :
					putenv('PROJECT_DOMAIN="' . $_SERVER['SERVER_NAME'] . '"');
				endif;
			else :
				putenv('PROJECT_DOMAIN="app.com"');
			endif;
		endif;

		if (getenv('PROJECT_DOMAIN') !== false) :
			return getenv('PROJECT_DOMAIN');
		elseif (array_key_exists('PROJECT_DOMAIN', $_ENV)) :
			return $_ENV['PROJECT_DOMAIN'];
		endif;

		return '';
	}

	private static function SupportDomain(): string
	{
		if (getenv('SUPPORT_DOMAIN') !== false) :
			return getenv('SUPPORT_DOMAIN');
		elseif (!array_key_exists('SUPPORT_DOMAIN', $_ENV) || $_ENV['SUPPORT_DOMAIN'] === false) :
			putenv('SUPPORT_DOMAIN="domain.support"');
		endif;

		if (getenv('SUPPORT_DOMAIN') !== false) :
			return getenv('SUPPORT_DOMAIN');
		elseif (array_key_exists('SUPPORT_DOMAIN', $_ENV)) :
			return $_ENV['SUPPORT_DOMAIN'];
		endif;

		return '';
	}

	private static function ProjectNamespace(): string
	{
		if (getenv('PROJECT_NAMESPACE') !== false) :
			return getenv('PROJECT_NAMESPACE');
		elseif (!array_key_exists('PROJECT_NAMESPACE', $_ENV) || $_ENV['PROJECT_NAMESPACE'] === false) :
			putenv('PROJECT_DOMAIN="app"');
		endif;

		if (getenv('PROJECT_NAMESPACE') !== false) :
			return getenv('PROJECT_NAMESPACE');
		elseif (array_key_exists('PROJECT_NAMESPACE', $_ENV)) :
			return $_ENV['PROJECT_NAMESPACE'];
		endif;

		return '';
	}

	private static function ServerAdmin(): string
	{
		if (getenv('SERVER_ADMIN') !== false) :
			return getenv('SERVER_ADMIN');
		elseif (!array_key_exists('SERVER_ADMIN', $_ENV) || !isset($_ENV['SERVER_ADMIN']) || $_ENV['SERVER_ADMIN'] === false || empty($_ENV['SERVER_ADMIN']) || !filter_var($_ENV['SERVER_ADMIN'], FILTER_VALIDATE_EMAIL)) :
			if (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) :
				$domain = explode('.', $_SERVER['SERVER_NAME']);
				if (count($domain) === 3) :
					return 'webmaster@' . $domain[1] . '.' . $domain[2];
				else :
					return 'webmaster@' . $_SERVER['SERVER_NAME'];
				endif;
			else :
				return 'webmaster@domain.com';
			endif;
		endif;

		if (getenv('SERVER_ADMIN') !== false) :
			return getenv('SERVER_ADMIN');
		elseif (array_key_exists('SERVER_ADMIN', $_ENV)) :
			return $_ENV['SERVER_ADMIN'];
		endif;

		return '';
	}

	private static function GetLogPath(): string|bool
	{
		if (getenv('LOG_PATH') !== false) :
			$path = getenv('LOG_PATH');
		elseif (!array_key_exists('LOG_PATH', $_ENV) || $_ENV['LOG_PATH'] === false) :
			$path = $_SERVER["HOME"] . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'php_log';
			putenv('LOG_PATH="' . $path . '"');
		endif;


		if (getenv('LOG_PATH') !== false) :
			$path = getenv('LOG_PATH');
		elseif (array_key_exists('LOG_PATH', $_ENV)) :
			$path = $_ENV['LOG_PATH'];
		endif;

		if (!file_exists($path)) :
			touch($path);
		endif;

		return realpath($path);
	}

	private static function getEmail(?int $errno = null): string
	{
		$email = self::ServerAdmin();
		if ($errno === null) :
			return $email;
		endif;
		switch ($errno) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
			case E_NOTICE:
			case E_USER_NOTICE:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				if (array_key_exists('SERVER_NAME', $_SERVER)) :
					$email = $_SERVER['SERVER_NAME'];
					if (str_contains($_SERVER['SERVER_NAME'], 'portal.')) :
						$email = 'portal.' . self::ProjectDomain() . '@' . self::SupportDomain();
					endif;
				endif;
				break;
			default:
				$email = self::ProjectNamespace() . '@' . self::SupportDomain();
				break;
		}

		return $email;
	}

	private static function getColor(?int $errno = null): string
	{
		$color = 'notice';
		switch ($errno) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$color = 'danger';
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				$color = 'warning';
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$color = 'notice';
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$color = 'info';
				break;
			default:
				$color = 'danger';
				break;
		}
		return $color;
	}

	private static function getSubject(?int $errno = null): string
	{
		$errstr = self::Project();
		$subject = '';
		switch ($errno) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$subject = "$errstr: ERROR [$errno]";
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				$subject = "$errstr: WARNING [$errno]";
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$subject = "$errstr: NOTICE [$errno]";
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				/* Don't execute PHP internal error handler */
				$subject = "$errstr: DEPRECATED [$errno]";
				break;
			default:
				$subject = "$errstr: Unknown error type: [$errno]";
				break;
		}
		return $subject;
	}

	private static function getMessage(?int $errno, string $errline, string $errfile, string $errstr): string
	{
		$message = '';
		switch ($errno) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$message = "Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ")";
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				$message = "Warning on line $errline in file $errfile";
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$message = "Notice on line $errline in file $errfile";
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				/* Don't execute PHP internal error handler */
				$message = "Deprecated on line $errline in file $errfile";
				break;
			default:
				$message = "Unknown error on line $errline in file $errfile";
				break;
		}
		return $message . ': ' . $errstr;
	}


	/**
	 * mail error to the developer
	 *
	 * @param  string $subject
	 * @param  string|null $message
	 * @param  string $email
	 * @return bool true on success or false on failure.
	 */
	public static function mail_error(string $subject, string|null $message, string $email): bool
	{
		if (defined('DEBUG') && DEBUG) :
			\ob_clean();
			header('Content-Type: text/markdown');
			print $message;
			die();
		endif;

		$email = self::getEmail();
		$headers = array();

		// To send HTML mail, the Content-type header must be set
		//$headers[] = 'MIME-Version: 1.0';
		//$headers[] = 'Content-type: text/html; charset=iso-8859-1';
		$headers[] = 'X-Mailer: PHP/' . phpversion();
		$headers[] = 'From: ' . self::Project() . ' Webmaster <webmaster@' . self::ProjectDomain() . '>';
		if (!DEBUG) :
			$headers[] = 'CC: ' . self::Project() . ' <' . self::ServerAdmin() . '>';
		endif;
		if (isset($_SESSION['email']) && !empty($_SESSION['email']) && filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) :
			$headers[] = 'Reply-To: ' . (isset($_SESSION['name']) ? $_SESSION['name'] : self::Project() . ' Webmaster') . ' <' . $_SESSION['email'] . '>';
		endif;
		//return mail(ADMIN_MAIL, self::Project() . $subject, 'PHP ' . $subject . ':' .  $message, $headers);;
		return error_log('PHP ' . $subject . ': ' . $message, 1, $email, implode("\r\n", $headers));
	}


	/**
	 * Error handler, passes flow over the exception logger with new ErrorException.
	 *
	 * @param  int  $errno
	 * @param  string  $errstr
	 * @param  string  $errfile
	 * @param  int $errline
	 * @uses mail_error
	 * @return bool  true on success or false on failure.
	 */
	public static function log_error($errno, $errstr, $errfile, $errline) : bool
	{
		// $errstr may need to be escaped:
		$errstr = htmlspecialchars($errstr);


		Log::error_message($message);

		self::mail_error(
			self::getSubject($errno),
			self::getMessage($errno, $errline, $errfile, $errline, $errstr),
			self::getEmail($errno)
		);

		return self::log_exception(new ErrorException($errstr, 0, $errno, $errfile, $errline));

		/* Don't execute PHP internal error handler */
		return true;
	}

	/**
	 * Uncaught exception handler.
	 */
	public static function log_exception(Exception $e): bool
	{
		if (DEBUG) :
			print "<div style='text-align: center;'>";
			print "<h2 style='color: rgb(190, 50, 50);'>Exception Occurred:</h2>";
			print "<table style='width: 800px; display: inline-block;'>";
			print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>" . get_class($e) . "</td></tr>";
			print "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$e->getMessage()}</td></tr>";
			print "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$e->getFile()}</td></tr>";
			print "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$e->getLine()}</td></tr>";
			print "</table></div>";
		elseif ($e !== null) :
			// Log the exception
			$message = array(date('Y-m-d H:i:s'));
			$message[] = "Type: " . get_class($e);
			$message[] = "Message: " . $e->getMessage();
			$message[] = "File: " . $e->getFile();
			$message[] = "Line: " . $e->getLine();
			$message = implode(";\t", $message;
			file_put_contents(self::GetLogPath(), date('Y-m-d H:i:s') . "\t" . $message . PHP_EOL, FILE_APPEND);
		endif;
		$errstr = $e->getMessage();
		$errno = $e->getCode();
		$errfile = $e->getFile();
		$errline = $e->getLine();
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting, so let it fall
			// through to the standard PHP error handler
			return false;
		}

		// $errstr may need to be escaped:
		$errstr = htmlspecialchars($errstr);

		$email = self::getEmail($errno);
		$subject = self::getSubject($errno);
		$message = self::getMessage($errno, $errline, $errfile, $errstr);
		$color = self::getColor($errno);


		Log::error_message($message);
		$email = self::ServerAdmin();
		if (!DEBUG && ($errno !== (E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE))) :
			self::mail_error(
				$subject,
				$message,
				$email
			);
		elseif (DEBUG) :
			Log::error_handler(
				$color,
				$subject,
				$message
			);
		elseif (!DEBUG) :
			Log::error_handler(
				$color,
				$subject,
				$message
			);
		endif;

		/* Don't execute PHP internal error handler */
		return true;
	}

	/**
	 * PHP shutdown function
	 *
	 * @return void
	 */
	public static function shutdown_function()
	{
		$error = \error_get_last();

		if ($error != null) :
			if ($error["type"] == E_ERROR) :
				self::log_error($error["type"], $error["message"], $error["file"], $error["line"]);
			endif;
			\ob_clean();
			// Fatal error, E_ERROR === 1
			if ($error['type'] === E_ERROR) :
				extract($error);

				global $mysqli;
				if (!empty($mysqli->error)) {
					$message .= $mysqli->error;
				}

				if (!DEBUG) :
					self::mail_error(
						"Error",
						"in '$file', line $line:\r\n\r\n$message\n",
						self::ProjectNamespace() . '@' . self::SupportDomain()
					);
					\http_response_code(500);

				else :
					echo '<script>document.getElementById("loading").style.display = "none";</script>';
				endif;
			endif;
		endif;

		if (\http_response_code() === 500) :
			header('location: /oops/500');
			exit();
		endif;
	}
}
