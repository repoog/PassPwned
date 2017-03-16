<?php

/**
 * PassPwned Database Operation Class
 * Created by: Cooper Pei
 * Created date: 2016/5/9
 */

require(dirname(dirname(__FILE__)) . '/config.php');

class DB
{
	// MySQL result, which is either a resource or boolean.
	protected $result;
	// Results of the last query mode.
	protected $last_result;
	// Whether successfully connect to database
	protected $is_connected = FALSE;
	// Whether the database queries are ready to start executing.
	protected $is_ready = FALSE;
	
	// Database Username
	private $db_user;
	// Database Password
	private $db_password;
	// Database Name
	private $db_name;
	// Database Host
	private $db_host;
	// Database connection charset
	private $charset;
	// Database table prefix
	private $db_prefix;
	// Database Handle
	private $db_handle;
	
	// DB class instance
	private static $_instance = NULL;
	
	// Connects to the database server and selects a database.
	private function __construct($db_user, $db_password, $db_name, $db_host, $db_prefix) {
		$this->db_user 		= $db_user;
		$this->db_password	= $db_password;
		$this->db_name		= $db_name;
		$this->db_host		= $db_host;
		$this->db_prefix	= $db_prefix;
		$this->db_connect();
	}

	function __destruct() {
        mysqli_close($this->db_handle);
    }

	// Connect to database.
	private function db_connect() {
		$this->db_handle = mysqli_init();
		$client_flags = defined('MYSQL_CLIENT_FLAGS') ? MYSQL_CLIENT_FLAGS : 0;
		mysqli_real_connect($this->db_handle, $this->db_host, $this->db_user, $this->db_password,
                            $this->db_name, NULL, NULL, $client_flags);
		
		if (!$this->db_handle) {
			$this->db_handle = NULL;
			$this->show_error(mysqli_connect_error(), mysqli_connect_errno());
			return FALSE;
		}else {
			if (!$this->is_connected) {
				$this->charset = DB_CHARSET;
			}
			$this->is_connected = TRUE;
			$this->is_ready = TRUE;
			mysqli_set_charset($this->db_handle, $this->charset);
			return TRUE;
		}
	}
	
    // Generate instance of DB class.
	public static function get_instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new DB(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, DB_PREFIX);
		}
		return self::$_instance;
	}
	
	// Query data from database.
	public function query($query_statement, $args=NULL) {
		if (!$this->is_ready) {
			return FALSE;
		}
		
		if (!empty($this->db_handle)) {
			if (strpos($query_statement, '%')) {
				$query_statement = $this->prepare($query_statement, $args);
			}
			$this->result = mysqli_query($this->db_handle, $query_statement);
			if (mysqli_errno($this->db_handle)) {
				return FALSE;
			}
		}

        $this->last_result = array();
        $num_rows = 0;
        while ($obj_row = mysqli_fetch_object($this->result)) {
            if ($obj_row != null) {
                $this->last_result[$num_rows] = $obj_row;
                $num_rows++;
            }
        }
		return $this->last_result;
	}

    // Update API called count number
	public function update($update_statement, $args=NULL) {
		if (!$this->is_ready) {
			return FALSE;
		}
		
		if (!empty($this->db_handle)) {
            if (strpos($update_statement, '%')) {
                $update_statement = $this->prepare($update_statement, $args);
            }
			$this->result = mysqli_query($this->db_handle, $update_statement);
		}
		
		if (!$this->result) {
            $this->show_error('Update failed');
		}
	}

	// Prepares a SQL query for safe execution.
	private function prepare($sql_statement, $args) {
		if (is_null($sql_statement))
			return;
		
		$args = func_get_args();
		array_shift($args);
		// If arguments were passed as an array, move them up
		if (isset($args[0]) && is_array($args[0]))
			$args = $args[0];
		// In case someone mistakenly already single quoted it.
		$sql_statement = str_replace("'%s'", '%s', $sql_statement);
		// Double quote unquoting situation.
		$sql_statement = str_replace('"%s"', '%s', $sql_statement);
		// Force floats to be locale unaware
		$sql_statement = preg_replace('|(?<!%)%f|' , '%F', $sql_statement);
		// Quote the strings, avoiding escaped strings like %%s
		$sql_statement = preg_replace('|(?<!%)%s|', "'%s'", $sql_statement);
		array_walk($args, array($this, 'escape_by_ref'));
		
		return @vsprintf($sql_statement, $args);
	}
	
	// Escapes content by reference for insertion into the database.
	private function escape_by_ref(&$string) {
		if (!is_float($string))
			return mysqli_real_escape_string($this->db_handle, $string);
	}

	// Show error message in a nice style.
	private function show_error($message, $error_code = '500') {
		die('ERROR CODE: ' . $error_code . '<br />ERROR MESSAGE: ' . $message);
	}
}