<?php

/**
 * PassPwned Database Operation Class
 * Created by: Cooper Pei
 * Created date: 2016/5/9
 * Update date: 2019/1/17
 */

require(dirname(dirname(__FILE__)) . '/config.php');

class DB
{
	// MySQL result, which is either a resource or boolean.
	protected $result;
	// Results of the last query mode.
	protected $last_result = array();
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
		$this->db_handle = new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_name);

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
            $this->db_handle->set_charset($this->charset);
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
	public function query($query_stmt, $params=NULL) {
		if (!$this->is_ready) {
			return FALSE;
		}
		
		if (!empty($this->db_handle)) {
            $stmt = $this->db_handle->prepare($query_stmt);
            if (isset($params)) {
                call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));
            }
            $this->result = $stmt->execute();
            $meta = $stmt->result_metadata();

            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }

            call_user_func_array(array($stmt, 'bind_result'), $this->refValues($parameters));
            while ($stmt->fetch()) {
                $x = array();
                foreach($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $this->last_result = isset($results) ? $results : [];
		}

		return $this->last_result;
	}

    // Update data to database.
	public function update($update_stmt, $params=NULL) {
		if (!$this->is_ready) {
			return FALSE;
		}
		
		if (!empty($this->db_handle)) {
            $stmt = $this->db_handle->prepare($update_stmt);
            if (isset($params)) {
                call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));
            }
            $this->result = $stmt->execute();
		}
		
		if ($this->result) {
            return TRUE;
		}else {
		    return FALSE;
        }
	}

	// Insert data to database.
    public function insert($insert_stmt, $params=NULL) {
		if (!$this->is_ready) {
			return FALSE;
		}

		if (!empty($this->db_handle)) {
            $stmt = $this->db_handle->prepare($insert_stmt);
            if (isset($params)) {
                call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));
            }
			$this->result = $stmt->execute();
		}

		if ($this->result) {
		    return TRUE;
		}else {
		    return FALSE;
        }
	}

    private function refValues($arr){
        if (strnatcmp(phpversion(), '5.3') >= 0) //Reference is required for PHP 5.3+
        {
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }

	// Show error message in a nice style.
	private function show_error($message, $error_code = '500') {
		die('ERROR CODE: ' . $error_code . '<br />ERROR MESSAGE: ' . $message);
	}
}