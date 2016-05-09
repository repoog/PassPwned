<?php
/**
 * PassPwned DB Class
 */

class ppdb {
	// Amount of queries made
	public $num_queries = 0;
	// MySQL result, which is either a resource or boolean.
	protected $result;
	// Results of the last query mode.
	protected $last_result;
	// Whether successfully connect to database
	protected $has_connected = false;
	// Whether the database queries are ready to start executing.
	protected $ready = false;
	// Database Username
	protected $dbuser;
	// Database Password
	protected $dbpassword;
	// Database Name
	protected $dbname;
	// Database Host
	protected $dbhost;
	// Database Handle
	protected $dbh;
	
	// Connects to the database server and selects a database.
	public function __construct($dbuser, $dbpassword, $dbname, $dbhost) {
		$this->dbuser = $dbuser;
		$this->dbpassword = $dbpassword;
		$this->dbname = $dbname;
		$this->dbhost = $dbhost;
		$this->db_connect();
	}
	
	// Destroy and release database object. 
	public function __destruct() {
		return true;
	}
	
	// Connect to and select database.
	public function db_connect() {
		$this->dbh = mysqli_init();
		$client_flags = defined('MYSQL_CLIENT_FLAGS') ? MYSQL_CLIENT_FLAGS : 0;
		$port = null;
		$socket = null;
		mysqli_real_connect($this->dbh, $this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname, $port, $socket, $client_flags);
		
		if (!$this->dbh) {
			$this->dbh = null;
			$this->showerror(mysqli_connect_error(), mysqli_connect_errno());
			return false;
		}else {
			if (!$this->has_connected) {
				$this->charset = DB_CHARSET;
			}
			$this->has_connected = true;
			$this->ready = true;
			mysqli_set_charset($this->dbh, $this->charset);
			return true;
		}
		return false;
	}
	
	// Query data existence from database
	public function query($query, $detail = false) {
		if (!$this->ready) {
			return false;
		}
		
		if (!empty($this->dbh)) {
			$this->result = mysqli_query($this->dbh, $query);
		}
		$this->num_queries++;
		
		// Output query result detail in attack mode,
		// else ouput query result rows in protect mode.
		if ($detail) {
			$this->last_result = array();
			$num_rows = 0;
			while ($obj_row = mysqli_fetch_object($this->result)) {
				if ($obj_row != null) {
					$this->last_result[$num_rows] = $obj_row;
					$num_rows++;
				}
			}
			return $this->last_result;
		}else {
			$this->rows_affected = mysqli_affected_rows($this->dbh);
			if ($this->rows_affected != -1) {
				return $this->rows_affected;
			}else {
				$this->showerror(mysqli_error($this->dbh), mysqli_errno($this->dbh));
			}
		}
	}
	
	// Prepares a SQL query for safe execution.
	public function prepare($query, $args) {
		if (is_null($query))
			return;
		
		if (strpos($query, '%') === false) {
			$this->showerror('The query argument of %s must have a placeholder.');
		}
		$args = func_get_args();
		array_shift($args);
		// If args were passed as an array, move them up
		if (isset($args[0]) && is_array($args[0]))
			$args = $args[0];
		$query = str_replace("'%s'", '%s', $query);		// in case someone mistakenly already singlequoted it
		$query = str_replace('"%s"', '%s', $query);		// doublequote unquoting
		$query = preg_replace('|(?<!%)%f|' , '%F', $query);		// Force floats to be locale unaware
		$query = preg_replace('|(?<!%)%s|', "'%s'", $query);	// quote the strings, avoiding escaped strings like %%s
		array_walk($args, array($this, 'escape_by_ref'));
		return @vsprintf($query, $args);
	}
	
	// Escapes content by reference for insertion into the database.
	public function escape_by_ref( &$string ) {
		if (!is_float($string))
			return mysqli_real_escape_string($this->dbh, $string);
	}
	
	// Show error messages in a nice style
	public function showerror($message, $error_code = '500') {
		die('ERROR CODE: ' . $error_code . '<br />ERROR MESSAGE: ' . $message);
	}
	
	// Closes the current database connection.
	public function close() {
		$update_sql = "UPDATE sod_site_apicall SET count=" . $this->num_queries;
		mysqli_query($this->dbh, $update_sql);
		if (!$this->dbh) {
			return false;
		}
		$closed = mysqli_close($this->dbh);
		if ($closed) {
			$this->dbh = null;
			$this->ready = false;
			$this->has_connected = false;
		}
		return $closed;
	}
}
?>