<?php
namespace KerkEnIT;
use Exception;
use ErrorException;
if (defined('DEBUG') && DEBUG) :
	error_reporting(E_ALL & ~E_DEPRECATED);
	ini_set('display_errors', 1);
else :
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
	ini_set('display_errors', 0);
endif;
/**
 * Log Class File for Kerk en IT Framework
 *
 * PHP versions 8.3, 8.4
 *
 * @package    KerkEnIT
 * @subpackage Log
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2024-2025 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since      Class available since Release 1.0.0
 **/
class Log {

	private static function buffer_output(string $param, string $color = null)
	{
		$rtn = '<div class="alert alert-' . ($color ?? 'info') . '">';
		$rtn .= $param;
		$rtn .= '</div>';
		echo $rtn;
	}

	public static function error_handler(string $color = null, ...$params)
	{
		if (defined('DEBUG') && DEBUG && php_sapi_name() == "cli") :
			var_dump($params);
		elseif (defined('DEBUG') && DEBUG) :
			if (ini_get('display_errors') == 0 && defined('DOWNLOAD') && \DOWNLOAD) :
				return null;
			endif;
			if (is_array($params)) :
				foreach ($params as $param) :
					if (!is_string($param)) :
						$param = json_encode($param);
					endif;
					error_log($param);
				endforeach;
			else :
				if (!is_string($params)) :
					$params = json_encode($params);
				endif;
				error_log($params);
			endif;
			if (is_array($params)) :
				foreach ($params as $param) :
					self::buffer_output($param);
				endforeach;
			else :
				if (!is_string($params)) :
					$params = json_encode($params);
				endif;
				self::buffer_output($param);
			endif;
		endif;
	}
	public static function log(...$params)
	{
		self::error_handler('notice', $params);
	}

	public static function info(...$params)
	{
		self::error_handler('info', $params);
	}

	public static function warn(...$params)
	{
		self::error_handler('warning', $params);
	}

	public static function error(...$params)
	{
		self::error_handler('danger', $params);
		if (defined('DEBUG') && DEBUG) :
			die();
		endif;
	}

	public static function error_message(string|null &$message) {
		global $mysqli;
		global $sql;

		$error = error_get_last();
		if ($error != null) :
			// Fatal error, E_ERROR === 1
			//if ($error['type'] === E_ERROR | E_USER_ERROR) :
			$message .= PHP_EOL . '> ' . $error['message'];
		//endif;
		endif;

		$stack = array_reverse(debug_backtrace());
		array_pop($stack);
		if(is_array($stack) && count($stack) > 0) :
			foreach($stack as $index => $trace) :
				if(array_key_exists('file', $trace) && !str_contains($trace['file'], '/class.Log.php')) :
					$message .= PHP_EOL . ($index +1) . '. File: ' . $trace['file'];
				endif;
				foreach ($trace as $key => $value) :
					if ($key !== 'file') :
						if(is_array($value)) :
							$message .= PHP_EOL . implode(PHP_EOL . '     - ', $value);
						else :
							$message .= PHP_EOL . '   - ' . ucfirst($key) . ': ' . json_encode($value);
						endif;
					endif;
				endforeach;
			endforeach;
		endif;

		if (!empty($mysqli->error)) {
			$message .= $mysqli->error;
		}

		if (isset($sql) && !empty($sql)) :
			$message .= '```' . $sql . '```';
		endif;
	}
}


if (!function_exists('varDump')) :
	/**
	 * var_dump all content
	 *
	 * @param  mixed $params
	 * @return void
	 */
	function varDump(...$params) {
		Log::log($params);
	}
endif;

if (!function_exists('varDie')) :
	function varDie(...$params)
	{
		Log::error($params);
		if (defined('DEBUG') && DEBUG) :
			die();
		endif;
	}
endif;



if (!function_exists('log')) :
	/**
	 * var_dump all content
	 *
	 * @param  mixed $params
	 * @return void
	 */
	function log(...$params)
	{
		varDump($params);
	}
endif;

if (!function_exists('error')) :
	function error(...$params)
	{
		varDie($params);
	}
endif;



/**
 * mail error to the developer
 *
 * @param  string $subject
 * @param  string|null $message
 * @param  string $email
 * @return bool true on success or false on failure.
 */
function mail_error(string $subject, string|null $message, string $email): bool
{
	Log::error_message($message);
	if (defined('DEBUG') && DEBUG) :
		header('Content-Type: text/markdown');
		print $message;
		die();
	endif;

	$email = getenv('ADMIN_MAIL');
	$headers = array();

	// To send HTML mail, the Content-type header must be set
	//$headers[] = 'MIME-Version: 1.0';
	//$headers[] = 'Content-type: text/html; charset=iso-8859-1';
	$headers[] = 'X-Mailer: PHP/' . phpversion();
	$headers[] = 'From: ' . getenv('PROJECT') . ' Webmaster <webmaster@' . getenv('PROJECT_DOMAIN') . '>';
	if (!DEBUG) :
		$headers[] = 'CC: ' . getenv('PROJECT') . ' <' . getenv('ADMIN_MAIL') . '>';
	endif;
	if (isset($_SESSION['email']) && !empty($_SESSION['email']) && filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) :
		$headers[] = 'Reply-To: ' . (isset($_SESSION['name']) ? $_SESSION['name'] : getenv('PROJECT') . ' Webmaster') . ' <' . $_SESSION['email'] . '>';
	endif;
	//return mail(ADMIN_MAIL, getenv('PROJECT') . $subject, 'PHP ' . $subject . ':' .  $message, $headers);;
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
function log_error($errno, $errstr, $errfile, $errline)
{
	// $errstr may need to be escaped:
	$errstr = htmlspecialchars($errstr);
	$color = 'notice';

	$email = getenv('ADMIN_MAIL');
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
					$email = 'portal.' . getenv('PROJECT_DOMAIN') . '@' . getenv('SUPPORT_DOMAIN');
				endif;
			endif;
			break;
		default:
			$email = getenv('PROJECT_NAMESPACE') . '@' . getenv('SUPPORT_DOMAIN');
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
			$email = 'portal.' . getenv('PROJECT_DOMAIN') . '@' . getenv('SUPPORT_DOMAIN');
		endif;
	endif;

	Log::error_message($message);
	$email = getenv('ADMIN_MAIL');
	mail_error(
		$subject,
		$message,
		$email
	);

	log_exception(new ErrorException($errstr, 0, $errno, $errfile, $errline));
}

/**
 * Uncaught exception handler.
 */
function log_exception(Exception $e): bool
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

	$email = getenv('ADMIN_MAIL');
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
					$email = 'portal.' . getenv('PROJECT_DOMAIN') . '@' . getenv('SUPPORT_DOMAIN');
				endif;
			endif;
			break;
		default:
			$email = getenv('PROJECT_NAMESPACE') . '@' . getenv('SUPPORT_DOMAIN');
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
			$email = 'portal.' . getenv('PROJECT_DOMAIN') . '@' . getenv('SUPPORT_DOMAIN');
		endif;
	endif;

	Log::error_message($message);
	$email = getenv('ADMIN_MAIL');
	if (!DEBUG && ($errno !== (E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE | E_USER_NOTICE))) :
		mail_error(
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
function my_shutdown_function(): void
{

	$error = error_get_last();
	if ($error["type"] == E_ERROR) :
		//var_dump($error);
		//die();
		log_error($error["type"], $error["message"], $error["file"], $error["line"]);
	endif;
	//if ($error != null) :
	//	// Fatal error, E_ERROR === 1
	//	if ($error['type'] === E_ERROR) :
	//		extract($error);

	//		global $mysqli;
	//		if (!empty($mysqli->error)) {
	//			$message .= $mysqli->error;
	//		}

	//		if (!DEBUG) :
	//			mail_error(
	//				"Error",
	//				"in '$file', line $line:\r\n\r\n$message\n",
	//				getenv('PROJECT_NAMESPACE') . '@' . getenv('SUPPORT_DOMAIN')
	//			);
	//			http_response_code(500);
	//			header('location: /oops/500');
	//		else :
	//			echo '<script>document.getElementById("loading").style.display = "none";</script>';
	//		endif;
	//	endif;
	//endif;
}
//register_shutdown_function('my_shutdown_function');
//set_error_handler('log_error');
?>