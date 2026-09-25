<?php

namespace KerkEnIT;

/**
 * Database
 *
 * A thin mysqli wrapper that mimics the SQLite3 API used across this project,
 * so the existing call sites can be migrated to MySQL with minimal changes.
 *
 * @package    KerkEnIT
 * @subpackage Database
 * @author     Marco van 't Klooster <info@kerkenit.nl>
 * @copyright  2026 © Kerk en IT
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License v3.0
 * @link       https://www.kerkenit.nl
 * @since 	   Class available since Release 1.2.1
 *
 * @requires   PHP extension "mysqli" (ext-mysqli)
 **/
class Database
{
	/**
	 * The underlying mysqli connection.
	 *
	 * @var mysqli
	 */
	private \mysqli $mysqli;

	/**
	 * The last error message.
	 *
	 * @var string
	 */
	private string $lastError = '';

	/**
	 *
	 * Constructor for the Database class
	 *
	 * @param string      $host     MySQL host (e.g. "localhost")
	 * @param string      $user     MySQL username
	 * @param string      $password MySQL password
	 * @param string      $database MySQL database name
	 * @param int|null    $port     MySQL port (default: null → 3306)
	 * @param string|null $socket   MySQL socket path (default: null)
	 * @return void
	 */
	public function __construct(string $host, string $user, string $password, string $database, ?int $port = null, ?string $socket = null)
	{
		$this->mysqli = new \mysqli($host, $user, $password, $database, $port, $socket);
		if ($this->mysqli->connect_errno) :
			throw new \mysqli_sql_exception('Database connection failed: ' . $this->mysqli->connect_error, $this->mysqli->connect_errno);
		endif;
		$this->mysqli->set_charset('utf8mb4');
	}

	/**
	 *
	 * Run a query and return the result set.
	 *
	 * @param string $sql The SQL query to run.
	 * @return \mysqli_result|false The result set, or false on error.
	 */
	public function query(string $sql)
	{
		$result = $this->mysqli->query($sql);
		if ($result === false) :
			$this->lastError = $this->mysqli->error;
			return false;
		endif;
		return $result;
	}

	/**
	 *
	 * Execute a statement that returns no result set (INSERT/UPDATE/DELETE/DDL).
	 *
	 * @param string $sql The SQL statement to execute.
	 * @return bool TRUE on success, FALSE on error.
	 */
	public function exec(string $sql): bool|int
	{
		try {
			$result = $this->mysqli->query($sql);
		} catch (\mysqli_sql_exception $e) {
			if($e->getCode() == MySqlError::DUPLICATE_ENTRY) :
				return MySqlError::DUPLICATE_ENTRY;
			else :
				$this->lastError = $e->getMessage();
			endif;
			return false;
		}
		if ($result === false) :
			$this->lastError = $this->mysqli->error;
			return false;
		endif;
		return true;
	}

	/**
	 *
	 * Get the last error message.
	 *
	 * @return string
	 */
	public function lastErrorMsg(): string
	{
		return $this->lastError;
	}

	/**
	 *
	 * Escape a string for safe inclusion in a query (replaces SQLite3::escapeString).
	 *
	 * @param string $string The string to escape.
	 * @return string
	 */
	public function escapeString(string $string): string
	{
		return $this->mysqli->real_escape_string($string);
	}

	/**
	 *
	 * Close the database connection.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->mysqli->close();
	}
}

/**
 *
 * MySQL error codes.
 * Contains constants for common MySQL error codes.
 *
 * @since Class available since Release 1.2.1
 */
class MySqlError
{
	const DUPLICATE_ENTRY = 1062;
	const FOREIGN_KEY_CONSTRAINT = 1452;
}