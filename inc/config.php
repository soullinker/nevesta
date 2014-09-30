<?php

define('SERVER_SELF', 0);
define('DB_MAIN', 0);

$config = array();

$config['db'] = array(
	SERVER_SELF => array(
		'host'	=> '127.0.0.1',
		'user'	=> 'nvst',
		'pass'	=> '#Tx5:*B&Bs$kQ!K',
		'port'	=> 3306,
		'base'	=> array(
			DB_MAIN	=> 'nevesta'
		)
	)
);

?>