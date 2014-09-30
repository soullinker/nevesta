<?php

define('TYPE_NOCLEAN',      0); // no change

define('TYPE_BOOL',     1); // force boolean
define('TYPE_INT',      2); // force integer
define('TYPE_UINT',     3); // force unsigned integer
define('TYPE_NUM',      4); // force number
define('TYPE_UNUM',     5); // force unsigned number
define('TYPE_UNIXTIME', 6); // force unix datestamp (unsigned integer)
define('TYPE_STR',      7); // force trimmed string
define('TYPE_NOTRIM',   8); // force string - no trim
define('TYPE_NOHTML',   9); // force trimmed string with HTML made safe
define('TYPE_ARRAY',   10); // force array
define('TYPE_FILE',    11); // force file
define('TYPE_BINARY',  12); // force binary string
define('TYPE_NOHTMLCOND', 13); // force trimmed string with HTML made safe if determined to be unsafe

define('TYPE_ARRAY_BOOL',     101);
define('TYPE_ARRAY_INT',      102);
define('TYPE_ARRAY_UINT',     103);
define('TYPE_ARRAY_NUM',      104);
define('TYPE_ARRAY_UNUM',     105);
define('TYPE_ARRAY_UNIXTIME', 106);
define('TYPE_ARRAY_STR',      107);
define('TYPE_ARRAY_NOTRIM',   108);
define('TYPE_ARRAY_NOHTML',   109);
define('TYPE_ARRAY_ARRAY',    110);
define('TYPE_ARRAY_FILE',     11);  // An array of "Files" behaves differently than other <input> arrays. TYPE_FILE handles both types.
define('TYPE_ARRAY_BINARY',   112);
define('TYPE_ARRAY_NOHTMLCOND',113);

define('TYPE_ARRAY_KEYS_INT', 202);
define('TYPE_ARRAY_KEYS_STR', 207);

define('TYPE_CONVERT_SINGLE', 100); // value to subtract from array types to convert to single types
define('TYPE_CONVERT_KEYS',   200); // value to subtract from array => keys types to convert to single types

class Input
{
	public static $GPC = array();
	private static $GPCexists = array();

	private static $magic_quotes_gpc = true;

	private static $superglobal_lookup = array(
		'g' => '_GET',
		'p' => '_POST',
		'r' => '_REQUEST',
		'c' => '_COOKIE',
		's' => '_SERVER',
		'e' => '_ENV',
		'f' => '_FILES'
	);

	public static function init()
	{
		static::$magic_quotes_gpc = get_magic_quotes_gpc();
	}

	public static function &cleanArray(&$source, $variables)
	{
		$return = array();

		foreach ($variables AS $varname => $vartype)
		{
			$return[$varname] =& self::clean($source[$varname], $vartype, isset($source[$varname]));
		}

		return $return;
	}

	/**
	* Makes GPC variables safe to use
	*
	* @param	string	Either, g, p, c, r or f (corresponding to get, post, cookie, request and files)
	* @param	array	Array of variable names and types we want to extract from the source array
	*
	* @return	array
	*/
	public static function cleanArrayGPC($source, $variables)
	{
		$sg =& $GLOBALS[self::$superglobal_lookup[$source]];

		foreach ($variables AS $varname => $vartype)
		{
			if (!isset(self::$GPC[$varname])) // limit variable to only being "cleaned" once to avoid potential corruption
			{
				self::$GPCexists[$varname] = isset($sg[$varname]);
				self::$GPC[$varname] =& self::clean(
					$sg[$varname],
					$vartype,
					isset($sg[$varname])
				);
			}
		}
	}

	/**
	* Makes a single GPC variable safe to use and returns it
	*
	* @param	array	The source array containing the data to be cleaned
	* @param	string	The name of the variable in which we are interested
	* @param	integer	The type of the variable in which we are interested
	*
	* @return	mixed
	*/
	public static function &cleanGPC($source, $varname, $vartype = TYPE_NOCLEAN)
	{
		if (!isset(self::$GPC[$varname])) // limit variable to only being "cleaned" once to avoid potential corruption
		{
			$sg =& $GLOBALS[self::$superglobal_lookup[$source]];

			self::$GPCexists[$varname] = isset($sg[$varname]);
			self::$GPC[$varname] =& self::clean(
				$sg[$varname],
				$vartype,
				isset($sg[$varname])
			);
		}

		return self::$GPC[$varname];
	}

	/**
	* Makes a single variable safe to use and returns it
	*
	* @param	mixed	The variable to be cleaned
	* @param	integer	The type of the variable in which we are interested
	* @param	boolean	Whether or not the variable to be cleaned actually is set
	*
	* @return	mixed	The cleaned value
	*/
	public static function &clean(&$var, $vartype = TYPE_NOCLEAN, $exists = true)
	{
		if ($exists)
		{
			if ($vartype < TYPE_CONVERT_SINGLE)
			{
				self::doClean($var, $vartype);
			}
			else if (is_array($var))
			{
				if ($vartype >= TYPE_CONVERT_KEYS)
				{
					$var = array_keys($var);
					$vartype -=  TYPE_CONVERT_KEYS;
				}
				else
				{
					$vartype -= TYPE_CONVERT_SINGLE;
				}

				foreach (array_keys($var) AS $key)
				{
					self::doClean($var[$key], $vartype);
				}
			}
			else
			{
				$var = array();
			}
			return $var;
		}
		else
		{
			if ($vartype < TYPE_CONVERT_SINGLE)
			{
				switch ($vartype)
				{
					case TYPE_INT:
					case TYPE_UINT:
					case TYPE_NUM:
					case TYPE_UNUM:
					case TYPE_UNIXTIME:
					{
						$var = 0;
						break;
					}
					case TYPE_STR:
					case TYPE_NOHTML:
					case TYPE_NOTRIM:
					case TYPE_NOHTMLCOND:
					{
						$var = '';
						break;
					}
					case TYPE_BOOL:
					{
						$var = 0;
						break;
					}
					case TYPE_ARRAY:
					case TYPE_FILE:
					{
						$var = array();
						break;
					}
					case TYPE_NOCLEAN:
					{
						$var = null;
						break;
					}
					default:
					{
						$var = null;
					}
				}
			}
			else
			{
				$var = array();
			}

			return $var;
		}
	}

	/**
	* Does the actual work to make a variable safe
	*
	* @param	mixed	The data we want to make safe
	* @param	integer	The type of the data
	*
	* @return	mixed
	*/
	private static function &doClean(&$data, $type)
	{
		static $booltypes = array('1', 'yes', 'y', 'true', 'on');

		if (self::$magic_quotes_gpc)
			$data = stripslashes($data);

		switch ($type)
		{
			case TYPE_INT:    $data = intval($data);                                   break;
			case TYPE_UINT:   $data = ($data = intval($data)) < 0 ? 0 : $data;         break;
			case TYPE_NUM:    $data = strval($data) + 0;                               break;
			case TYPE_UNUM:   $data = strval($data) + 0;
							  $data = ($data < 0) ? 0 : $data;                         break;
			case TYPE_BINARY: $data = strval($data);                                   break;
			case TYPE_STR:    $data = trim(strval($data));                             break;
			case TYPE_NOTRIM: $data = strval($data);                                   break;
			case TYPE_NOHTML: $data = htmlspecialchars(trim(strval($data)));           break;
			case TYPE_BOOL:   $data = in_array(strtolower($data), $booltypes) ? 1 : 0; break;
			case TYPE_ARRAY:  $data = (is_array($data)) ? $data : array();             break;
			case TYPE_NOHTMLCOND:
			{
				$data = trim(strval($data));
				if (strcspn($data, '<>"') < strlen($data) OR (strpos($data, '&') !== false AND !preg_match('/&(#[0-9]+|amp|lt|gt|quot);/si', $data)))
				{
					// data is not htmlspecialchars because it still has characters or entities it shouldn't
					$data = htmlspecialchars($data);
				}
				break;
			}
			case TYPE_FILE:
			{
				// perhaps redundant :p
				if (is_array($data))
				{
					if (is_array($data['name']))
					{
						$files = count($data['name']);
						for ($index = 0; $index < $files; $index++)
						{
							$data['name'][$index] = trim(strval($data['name'][$index]));
							$data['type'][$index] = trim(strval($data['type'][$index]));
							$data['tmp_name'][$index] = trim(strval($data['tmp_name'][$index]));
							$data['error'][$index] = intval($data['error'][$index]);
							$data['size'][$index] = intval($data['size'][$index]);
						}
					}
					else
					{
						$data['name'] = trim(strval($data['name']));
						$data['type'] = trim(strval($data['type']));
						$data['tmp_name'] = trim(strval($data['tmp_name']));
						$data['error'] = intval($data['error']);
						$data['size'] = intval($data['size']);
					}
				}
				else
				{
					$data = array(
						'name'     => '',
						'type'     => '',
						'tmp_name' => '',
						'error'    => UPLOAD_ERR_NO_FILE, // UPLOAD_ERR_NO_FILE
						'size'     => 0,
					);
				}
				break;
			}
			case TYPE_UNIXTIME:
			{
				$data = ($data = intval($data)) < 0 ? 0 : $data;
				break;
			}
		}

		// strip out characters that really have no business being in non-binary data
		switch ($type)
		{
			case TYPE_STR:
			case TYPE_NOTRIM:
			case TYPE_NOHTML:
			case TYPE_NOHTMLCOND:
				$data = str_replace(chr(0), '', $data);
		}

		return $data;
	}
}

?>