<?php
namespace KerkEnIT;

/**
 * SQL
 * @author     Marco van 't Klooster, Kerk en IT <info@kerkenit.nl>
 */
class SQL {
	/**
	 * rows
	 *
	 * @var array
	 */
	private $rows;
	/**
	 * table name
	 *
	 * @var string
	 */
	public $table;

	/**
	 * where
	 *
	 * @var array
	 */
	private $where;

	/**
	 * mysqli
	 *
	 * @var object
	 */
	private $mysqli;

	/**
	 * Cache
	 *
	 * @var object
	 */
	private $_memcache_obj = null;

	/**
	 *
	 * Constructor for the SQL class
	 *
	 * @param mysqli $mysqli  MySQL link
	 * @param string $init_parameter  tablen ame
	 * @return void
	 */
	function __construct($mysqli, $init_parameter)
	{
		$this->mysqli = $mysqli;
		$this->table = $init_parameter;
	}

	/**
	 *
	 * Add a data column. Used for insert or update data
	 *
	 * @param string $column	Column name
	 * @param string $value		Data value. When type is ```NULL``` the string isn't formatted.
	 * @param bool $nullify		Default ```false```.  When ```true``` it will nullify when empty or not set
	 * @see SQL::varType()
	 * @return void
	 */
	public function addRow($column, $value, bool $nullify = false)
	{
		if($nullify) :
			if(!isset($value) || empty($value)) :
				$value = NULL;
			endif;
		endif;
		$this->rows[$column] = $value;
	}

	/**
	 *
	 * Add filter for the table with this data value
	 *
	 * @param string $column	Column name
	 * @param string $value		Data value
	 * @return void
	 */
	public function WHERE($column, $value)
	{
		$this->where[$column] = $value;
	}

	/**
	 *
	 * Filter table with given select is WHERE
	 *
	 * @param string $order	column to order
	 * @param bool $asc	order Ascending, false for Descending. Default true
	 * @param int $rowCount	count of rows to select. Default 1
	 * @return object|bool FALSE when no data found. Otherwise it will return the data column.
	 */
	public function SELECT($order = NULL, $asc = true, $rowCount = 1)
	{
		$sql = 'SELECT * FROM `' . $this->table . '` WHERE (0=0)';

		if (isset($this->where) && is_array($this->where) && count($this->where) > 0) :
			foreach ($this->where as $key => $value)
			{
				if(is_array($key)) :

					$tmp = array();
					foreach($key as $col)
					{
						$tmp[] = $col . '=' . $this->varType($value);
					}
					$sql .= ' AND (' . implode(' OR ', $tmp) . ')';
				elseif(is_array($value)) :
					$sql .= ' AND (';
					$tmp = array();
					foreach($value as $val)
					{
						$tmp[] = $key . '=' . $this->varType($val);
					}
					$sql .= ' AND (' . implode(' OR ', $tmp) . ')';
				else :
					$sql .= ' AND ' . $key . '=' . $this->varType($value);
				endif;
			}
		endif;

		if($order !== NULL) :
			$sql .= " ORDER BY `$order` " . ($asc ? 'ASC' : 'DESC');
		endif;
		//die($sql);
		if($rowCount === 1) :
			if ($result = $this->mysqli->query($sql)) :
				if ($result->num_rows > 0) :
					return $result->fetch_object();
				endif;
			endif;
			return false;
		elseif($rowCount > 1) :
			if ($result = $this->mysqli->query($sql)) :
				return $result->fetch_all(MYSQLI_ASSOC);
			endif;
			return false;
		endif;
	}

	/**
	 * strips the slashes from a string or array
	 *
	 * @param  string|array $array
	 * @return string|array type depend on input
	 */
	private function stripSlashes($array)
	{
		if (is_array($array)) :
			$out = array();
			foreach ($array as $key => $value)
			{
				$out[$key] = stripslashes($value);
			}
			return $out;
		else :
			return stripslashes($array);
		endif;
	}

	/**
	 *
	 * Insert data into the table
	 *
	 * @param string $redirect	Redirect URL
	 * @param bool $header	when TRUE redirect after successful insert
	 * @return bool TRUE when succeed, otherwise FALSE
	 */
	public function INSERT($redirect, $header = false)
	{
		$sql = 'INSERT INTO `' . $this->table . '` (';
		$values = ') VALUES (';
		$close = ')';
		foreach($this->rows as $column => $value)
		{
			$sql .= $column . ',';
			if($column == 'sortOrder' && $value == NULL) :
				$values = ') SELECT ';
				$close = ' FROM `' . $this->table . '`';
			endif;
		}
		$sql = substr($sql, 0, -1);

		$sql .= $values;
		foreach ($this->rows as $column => $value)
		{
			if ($column == 'id') :
				$sql .= '0,';
			elseif ($column == 'sortOrder' && $value == NULL) :
				$sql .= 'MAX(' . $column . ')+1,';
			else :
				$sql .= $this->varType($value) . ',';
			endif;

		}
		$sql = substr($sql, 0, -1);
		$sql .= $close . ';';

		if (!$this->mysqli->query($sql)) :
			if($this->mysqli->errno == 1062) :
				return 'DuplicateEntry';
			else :
				echo $this->message("danger", "Toevoegen item NIET gelukt. ERROR: " . $this->mysqli->error);
				if(DEBUG) :
					echo $this->message("info", $sql);
				endif;
			endif;
			return false;
		else :
			if (!$header) :
				echo $this->messageLink("success", "Toevoegen item gelukt.", $redirect);
			else :
				if(!empty($redirect)) :
					header('Location: ' . $redirect);
				endif;
			endif;
			return true;
		endif;
	}

	/**
	 *
	 * Update data into the table
	 *
	 * @param string $redirect	Redirect URL
	 * @param bool $header	when TRUE redirect after successful update
	 * @return bool TRUE when succeed, otherwise FALSE
	 */
	public function UPDATE($redirect, $header = false)
	{
		$sql = 'UPDATE `' . $this->table . '` SET ';
		foreach($this->rows as $key => $value)
		{
			$sql .= $key . '=' . $this->varType($value) .  ',';
		}
		$sql = substr($sql, 0, -1);
		$sql .=' WHERE (0=0)';

		if (count($this->where) > 0) :
			foreach($this->where as $key => $value)
			{
				$sql .= ' AND ' . $key . '=' . $this->varType($value);
			}
		endif;
		$sql .= ';';

		if (!$this->mysqli->query($sql)) :
			echo $this->message("danger", "Bijwerken item NIET gelukt. ERROR: " . $this->mysqli->error);
			if (DEBUG) :
				echo $this->message("info", $sql);
			endif;
			return false;
		else :
			if (!$header) :
				echo $this->messageLink("success", "Bijwerken item gelukt.", $redirect);
			else :
				if(!empty($redirect)) :
					header('Location: ' . $redirect);
				endif;
			endif;
			return true;
		endif;
	}

	/**
	 *
	 * Check if row existsis in the table
	 *
	 * @param string $column	Column to check
	 * @param string $value		Data value to compare
	 * @return bool TRUE when found, otherwise FALSE
	 */
	public function EXISTS($column, $value)
	{
		if ($result = $this->mysqli->query("SELECT COUNT(1) AS cnt FROM `".$this->table."` WHERE `" . $column . "`=" . $this->varType($value))) :
			if($result->fetch_object()->cnt > 0) :
				$result->close();
				return TRUE;
			else :
				$result->close();
			endif;
		endif;

		return FALSE;
	}

	/**
	 *
	 * Delete a row from the table
	 *
	 * @param string $id	Data ID
	 * @return bool
	 */
	public function DELETE($id)
	{
		return $this->mysqli->query("DELETE FROM `".$this->table."` WHERE ID='" . $id."'");
	}

	/**
	 *
	 * Delete a row from the table with select from WHERE
	 *
	 * @return bool
	 */
	public function DELETE_WHERE()
	{
		$sql = "DELETE FROM `".$this->table."` WHERE (0=0)";

		if (count($this->where) > 0) :
			foreach ($this->where as $key => $value)
			{
				$sql .= ' AND ' . $key . '=' . $this->varType($value);
			}
			return $this->mysqli->query($sql);
		endif;
		return FALSE;
	}

	public function DELETEFOREIGNKEY($redirect, $id, $message, $sqlSelect, $url)
	{
		if(!$this->mysqli->query("DELETE FROM `".$this->table."` WHERE ID=" . $id)) :
			echo $this->message("danger", "Verwijderen item niet gelukt.");
		elseif(!$this->mysqli->query($sql = "ALTER TABLE `".$this->table."` auto_increment = 1")) :
			echo $this->message("warning", "Opruimen database niet gelukt.");
		else :
			header('Location: '.$redirect);
		endif;
	}

	/**
	 * Format the type to the correct value
	 *
	 * @param  mixed $var Any type
	 * @return mixed|string
	 */
	public function varType($var)
	{
		if ($var == NULL) :
			return 'NULL';
		endif;
		$var = $this->mysqli->real_escape_string($var);
		if (strtolower($var) == 'null') :
			return 'NULL';
		elseif (is_string($var)) :
			return '"'.$var.'"';
		elseif (is_int($var)) :
			return ''.$var.'';
		elseif (is_float($var)) :
			return ''.$var.'';
		elseif (is_bool($var)) :
			return ''.$var.'';
		elseif (is_numeric($var)) :
			return ''.$var.'';
		else :
			return '"'.$var.'"';
		endif;
	}

	public function setSortOrder($table, $OldIndex, $Index, $ID)
	{
		if($result = $this->mysqli->query(sprintf('SELECT COUNT(1) AS cnt FROM `%s` WHERE account_ID = "%s"', $table, $_SESSION['account_ID']))) :
			$max = (int)$result->fetch_object()->cnt;
		endif;
		if($Index > 0):
			if ($Index < $OldIndex && $Index < $max) :
				if($result = $this->mysqli->query(sprintf("UPDATE `%s` SET sortOrder = (sortOrder + 1) WHERE sortOrder < %d AND sortOrder >= %d AND account_ID = '%s'", $table, $OldIndex, $Index, $_SESSION['account_ID']))) :
					if(!$result) :
						return $result->error();
					else :
						if($result = $this->mysqli->query(sprintf('UPDATE `%s` SET sortOrder = %d WHERE `ID` = "%s" AND account_ID = "%s"', $table, $Index, $ID, $_SESSION['account_ID']))) :
							if(! empty( $this->mysqli->error)) :
								return $this->mysqli->error;
							else :
								return true;
							endif;
						endif;
					endif;
				endif;
			elseif ($Index > $OldIndex && $Index <= $max) :
				if($result = $this->mysqli->query(sprintf("UPDATE `%s` SET sortOrder = (sortOrder - 1) WHERE sortOrder > %d AND sortOrder <= %d AND account_ID = '%s'", $table, $OldIndex, $Index, $_SESSION['account_ID']))) :
					if(!$result) :
						return $result->error();
					else :
						if($result = $this->mysqli->query(sprintf('UPDATE `%s` SET sortOrder = %d WHERE `ID` = "%s" AND account_ID = "%s"', $table, $Index, $ID, $_SESSION['account_ID']))) :
							if(! empty( $this->mysqli->error ) ) :
								return $this->mysqli->error;
							else:
								return true;
							endif;
						endif;
					endif;
				endif;
			endif;
		endif;
		return false;
	}

	private function ignoreDuplicateMessages($rtn)
	{
		if ($this->_memcache_obj == null) :
			$this->_memcache_obj = new Memcache;
			$this->_memcache_obj->connect('localhost', 11211);

		endif;

		$messages = $this->_memcache_obj->get('SQL_Messages');
		if($messages == false) :
			$messages = array();
		else :
			$messages = json_decode($messages);
		endif;
		if ($messages !== false && is_array($messages)) :
			if (in_array($rtn, $messages)) :
				return '';
			endif;
		endif;
		$messages[] = $rtn;
		$this->_memcache_obj->set('SQL_Messages', json_encode($messages), \MEMCACHE_COMPRESSED, 5);

		return $rtn;
	}

	public function message($color, $message)
	{
		$rtn = '';
		if (php_sapi_name() != "cli") :
			$rtn = '<div class="alert alert-' . ($color ?? 'success') . '">';
			$rtn .= $message;
			$rtn .= '</div>';
		endif;

		return $this->ignoreDuplicateMessages($rtn);
	}

	public function messageLink($color, $message, $url='')
	{
		$rtn = '';
		if (php_sapi_name() != "cli") :
			$rtn = '<div class="alert alert-' . ($color ?? 'danger') . '">';
			if(strlen($url) > 0) {
				$rtn .= '<a href="' . $url . '" class="alert-link">';
			}
			$rtn .= $message;
			if(strlen($url) > 0) {
				$rtn .= '</a>';
			}
			$rtn .= '</div>';
		endif;
		return $this->ignoreDuplicateMessages($rtn);
	}
}
?>