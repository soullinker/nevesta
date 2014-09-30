<?php

define('MYSQL_PERSISTENT', true);
define('MYSQL_CLOSE', false);

class DB {
	private static $_instance;

	private static $config = array();

	private static $connections = array();

	private static $link = null;
	private static $conn_id = 0;

	private static $sql = '';
	public static $sqls = array();

	public static $reporterror = false;

	private static $error = '', $errno = 0;

	private static $functions = array(
		'connect'				=> 'mysqli_connect',
		'pconnect'				=> 'mysqli_connect',
		'select_db'				=> 'mysqli_select_db',
		'query'					=> 'mysqli_query',
		'query_unbuffered'		=> 'mysqli_query', /* mysqli_unbuffered_query */
		'fetch_row'				=> 'mysqli_fetch_row',
		'fetch_array'			=> 'mysqli_fetch_array',
		'fetch_field'			=> 'mysqli_fetch_field',
		'fetch_object'			=> 'mysqli_fetch_object',
		'free_result'			=> 'mysqli_free_result',
		'data_seek'				=> 'mysqli_data_seek',
		'error'					=> 'mysqli_error',
		'errno'					=> 'mysqli_errno',
		'affected_rows'			=> 'mysqli_affected_rows',
		'num_rows'				=> 'mysqli_num_rows',
		'insert_id'				=> 'mysqli_insert_id',
		'escape_string'			=> 'mysqli_real_escape_string',
		'real_escape_string'	=> 'mysqli_real_escape_string',
		'close'					=> 'mysqli_close'
	);

	private static $locked = false;

	public static final function init()
	{
		if (!static::$_instance)
		{
			static::$_instance = new static();
			if (function_exists(static::$functions['real_escape_string']))
				static::$functions['escape_string'] = static::$functions['real_escape_string'];
		}

		return static::$_instance;
	}

	public static function setConfig($config_id, $config)
	{
		self::$config[$config_id] = $config;

		return static::$_instance;
	}

	public static function wrapConnection($host_id = 0, $pconnect = false)
	{
		self::$conn_id = $host_id;
		self::$link = isset(self::$connections[$host_id]) ? self::$connections[$host_id] : self::connect($host_id, $pconnect);

		return static::$_instance;
	}

	private static function connect($host_id, $pconnect)
	{
		foreach(self::$config[$host_id] as $key => &$val)
		{
			$$key = &$val;
			if ($key != 'base')
				unset($val);
		}
		//list ($host, $user, $pass, $port) = self::$config[$host_id];
		//unset(self::$config[$host_id]);
		self::$link = self::$connections[$host_id] = self::dbConnect("$host:$port", $user, $pass, $pconnect);
		return self::$link;
	}

	private static function dbConnect($hostport, $user, $pass, $pconnect)
	{
		$connect_function = self::$functions[$pconnect ? 'pconnect' : 'connect'];

		if ($link = $connect_function("$hostport", $user, $pass))
		{
			return $link;
		}
		else
		{
			self::halt('Can\'t connect to DB');
		}
	}

	public static function selectDB($db_id)
	{
		$db_select_function = self::$functions['select_db'];
		$db_name =& self::$config[self::$conn_id]['base'][$db_id];
		$db_select_function(self::$link, $db_name) or self::halt('Cannot use database ' . $db_name);
	}

	private static function executeQuery($buffered = true)
	{
		$query_function = self::$functions[$buffered ? 'query' : 'query_unbuffered'];

		if ($queryresult = $query_function(self::$link, self::$sql))
		{
			self::$sqls[] = self::$sql;
			self::$sql = '';

			return $queryresult;
		}
		else
		{
			self::halt('Failed to run query');
			self::$sql = '';
		}
	}

	public static function write($sql, $buffered = false)
	{
		self::$sql = & $sql;
		return self::executeQuery($buffered, self::$link);
	}

	public static function read($sql, $buffered = true)
	{
		self::$sql = & $sql;
		return self::executeQuery($buffered, self::$link);
	}

	public static function &first($sql, $type = MYSQL_ASSOC)
	{
		self::$sql = & $sql;
		$queryresult = self::executeQuery(true, self::$link);
		$returnresult = self::fetch($queryresult, $type);
		self::free($queryresult);
		return $returnresult;
	}

	public static function shutdownQuery($sql, $arraykey = -1)
	{
		if ($arraykey === - 1)
		{
			self::$shutdownqueries[] = $sql;
		}
		else
		{
			self::$shutdownqueries[$arraykey] = $sql;
		}
	}

	public static function numRows($queryresult)
	{
		$numrow_function = self::$functions['num_rows'];
		return @$numrow_function($queryresult);
	}

	public static function insertId()
	{
		$insertid_function = self::$functions['insert_id'];
		return @$insertid_function(self::$link);
	}

	public static function close()
	{
		$close_function = self::$functions['close'];
		return @$close_function(self::$link);
	}

	public static function escape($string)
	{
		$escape_function = self::$functions['escape_string'];
		return $escape_function(self::$link, $string);
	}

	public static function fetch($queryresult, $type = MYSQL_ASSOC)
	{
		$fetch_array_function = self::$functions['fetch_array'];
		return @$fetch_array_function($queryresult, $type);
	}

	public static function fetchRow($queryresult)
	{
		$fetch_row_function = self::$functions['fetch_row'];
		return @$fetch_row_function($queryresult);
	}

	public static function free($queryresult)
	{
		self::$sql = '';
		$free_result_function = self::$functions['free_result'];
		return @$free_result_function($queryresult);
	}

	public static function affectedRows()
	{
		$affected_rows_function = self::$functions['affected_rows'];
		return $affected_rows_function(self::$link);
	}

	public static function &object($sql, $class_name, $params = null)
	{
		self::$sql = & $sql;
		$queryresult = self::executeQuery(true, self::$link);
		$object = self::fetchObject($queryresult, $class_name, $params);
		self::free($queryresult);

		return $object;
	}

	public static function &objectList($sql, $class_name, $params = null)
	{
		self::$sql = & $sql;

		$objects = array();
		$result = self::executeQuery(false, self::$link);
		while ($obj = self::fetchObject($result, $class_name, $params))
		{
			$objects[] = $obj;
		}
		self::free($result);

		return $objects;
	}

	public static function fetchObject($queryresult, $class_name, &$params)
	{
		$fetch_obj_function = self::$functions['fetch_object'];
		if ($params === null)
		{
			return $fetch_obj_function($queryresult, $class_name);
		}
		else
		{
			return $fetch_obj_function($queryresult, $class_name, $params);
		}
	}

	public static function lockTables($tablelist)
	{
		if (! empty($tablelist) and is_array($tablelist))
		{
			$sql = '';
			foreach ($tablelist as $name => $type)
			{
				$sql .= (! empty($sql) ? ', ' : '') . TABLE_PREFIX . $name . " " . $type;
			}

			self::write("LOCK TABLES $sql");
			self::$locked = true;

		}
	}

	public static function unlockTables()
	{
		if (self::$locked)
		{
			self::write('UNLOCK TABLES');
		}
	}

	private static function error()
	{
		if (self::$link === null)
		{
			self::$error = '';
		}
		else
		{
			$error_function = self::$functions['error'];
			self::$error = $error_function(self::$link);
		}
	}

	private static function errno()
	{
		if (self::$link === null)
		{
			self::$errno = 0;
		}
		else
		{
			$errno_function = self::$functions['errno'];
			self::$errno = $errno_function(self::$link);
		}
	}

	private static function halt($errortext = '')
	{
		if (self::$link)
		{
			self::error();
			self::errno();
		}

		echo '<!-- ' . self::$errno . ' : ' . htmlspecialchars(self::$error) . ' -->';
	}
}

?>