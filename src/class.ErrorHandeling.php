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

class ErrorHandeling
{
	private static function Project(): string
	{
		if($_ENV['PROJECT'] === false) :
			putenv('PROJECT="My Fantastic App"');
		endif;
		return $_ENV['PROJECT'];
	}

	private static function ProjectDomain(): string
	{
		if ($_ENV['PROJECT_DOMAIN'] === false) :
			if(isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)) :
				$domain = explode('.', $_SERVER['SERVER_NAME']);
				if(count($domain) === 3) :
					putenv('PROJECT_DOMAIN="' . $domain[1] . '.' . $domain[2] . '"');
				else :
					putenv('PROJECT_DOMAIN="' . $_SERVER['SERVER_NAME'] . '"');
				endif;
			else :
				putenv('PROJECT_DOMAIN="app.com"');
			endif;
		endif;
		return $_ENV['PROJECT_DOMAIN'];
	}

	private static function SupportDomain(): string
	{
		if ($_ENV['SUPPORT_DOMAIN'] === false) :
			putenv('SUPPORT_DOMAIN="domain.support"');
		endif;
		return $_ENV['PROJECT_DOMAIN'];
	}

	private static function ProjectNamespace(): string
	{
		if ($_ENV['PROJECT_NAMESPACE'] === false) :
			putenv('PROJECT_DOMAIN="app"');
		endif;
		return $_ENV['PROJECT_NAMESPACE'];
	}

	private static function ServerAdmin(): string
	{
		if(!isset($_ENV['SERVER_ADMIN']) || $_ENV['SERVER_ADMIN'] === false || empty($_ENV['SERVER_ADMIN']) || !filter_var($_ENV['SERVER_ADMIN'], FILTER_VALIDATE_EMAIL)) :
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
		return $_ENV['SERVER_ADMIN'];
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

		$email = self::ServerAdmin();
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
	public static function log_error($errno, $errstr, $errfile, $errline)
	{
		// $errstr may need to be escaped:
		$errstr = htmlspecialchars($errstr);
		$color = 'notice';

		$email = self::ServerAdmin();
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

		switch ($errno) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$subject = "ERROR [$errno] $errstr";
				$message = "Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ")";
				$color = 'danger';
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				$subject = "WARNING [$errno] $errstr";
				$message = "Warning on line $errline in file $errfile";
				$color = 'warning';
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$subject = "NOTICE [$errno] $errstr";
				$message = "Notice on line $errline in file $errfile";
				$color = 'notice';
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				/* Don't execute PHP internal error handler */
				$subject = "DEPRECATED [$errno] $errstr";
				$message = "Deprecated on line $errline in file $errfile";
				$color = 'info';
				break;
			default:
				$subject = "Unknown error type: [$errno] $errstr";
				$message = "Unknown error on line $errline in file $errfile";
				$color = 'danger';
				break;
		}

		if (array_key_exists('SERVER_NAME', $_SERVER)) :
			$email = $_SERVER['SERVER_NAME'];
			if (str_contains($_SERVER['SERVER_NAME'], 'portal.')) :
				$email = 'portal.' . self::ProjectDomain() . '@' . self::SupportDomain();
			endif;
		endif;

		Log::error_message($message);
		$email = self::ServerAdmin();
		self::mail_error(
			$subject,
			$message,
			$email
		);

		self::log_exception(new ErrorException($errstr, 0, $errno, $errfile, $errline));
	}

	/**
	 * Uncaught exception handler.
	 */
	public static function  log_exception(Exception $e): bool
	//function log_error(int $errno, string $errstr, string $errfile, int $errline) : bool
	{
		if (DEBUG) {
			print "<div style='text-align: center;'>";
			print "<h2 style='color: rgb(190, 50, 50);'>Exception Occurred:</h2>";
			print "<table style='width: 800px; display: inline-block;'>";
			print "<tr style='background-color:rgb(230,230,230);'><th style='width: 80px;'>Type</th><td>" . get_class($e) . "</td></tr>";
			print "<tr style='background-color:rgb(240,240,240);'><th>Message</th><td>{$e->getMessage()}</td></tr>";
			print "<tr style='background-color:rgb(230,230,230);'><th>File</th><td>{$e->getFile()}</td></tr>";
			print "<tr style='background-color:rgb(240,240,240);'><th>Line</th><td>{$e->getLine()}</td></tr>";
			print "</table></div>";
		} else {
			$message = "Type: " . get_class($e) . "; Message: {$e->getMessage()}; File: {$e->getFile()}; Line: {$e->getLine()};";
			//file_put_contents($config["app_dir"] . "/tmp/logs/exceptions.log", $message . PHP_EOL, FILE_APPEND);
			//header("Location: {$config["error_page"]}");
		}

		exit();
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting, so let it fall
			// through to the standard PHP error handler
			return false;
		}

		// $errstr may need to be escaped:
		$errstr = htmlspecialchars($errstr);
		$color = 'notice';

		$email = self::ServerAdmin();
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

		switch ($errno) {
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
				$subject = "ERROR [$errno] $errstr";
				$message = "Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ")";
				$color = 'danger';
				break;
			case E_WARNING:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
				$subject = "WARNING [$errno] $errstr";
				$message = "Warning on line $errline in file $errfile";
				$color = 'warning';
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$subject = "NOTICE [$errno] $errstr";
				$message = "Notice on line $errline in file $errfile";
				$color = 'notice';
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				/* Don't execute PHP internal error handler */
				$subject = "DEPRECATED [$errno] $errstr";
				$message = "Deprecated on line $errline in file $errfile";
				$color = 'info';
				break;
			default:
				$subject = "Unknown error type: [$errno] $errstr";
				$message = "Unknown error on line $errline in file $errfile";
				$color = 'danger';
				break;
		}

		if (array_key_exists('SERVER_NAME', $_SERVER)) :
			$email = $_SERVER['SERVER_NAME'];
			if (str_contains($_SERVER['SERVER_NAME'], 'portal.')) :
				$email = 'portal.' . self::ProjectDomain() . '@' . self::SupportDomain();
			endif;
		endif;

		Log::error_message($message);
		$email = self::ServerAdmin();
		if (!DEBUG && ($errno !== (E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE))) :
			self::mail_error(
				$subject,
				$message,
				$email
			);


			if (DEBUG) :
				Log::error_handler(
					$color,
					$subject,
					$message
				);
			endif;
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
	public static function  shutdown_function()
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

		if(\http_response_code() === 500) :
			header('location: /oops/500');
			exit();
		endif;
	}


}
$error = new ErrorHandeling();
set_error_handler(array($error, 'log_error'));
register_shutdown_function(array($error, 'shutdown_function'));
//set_error_handler(array(ErrorHandeling, 'log_error'));
////register_shutdown_function('my_shutdown_function');
////set_error_handler('my_log_error');
