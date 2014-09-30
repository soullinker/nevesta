<?php

define('DIR', dirname(__FILE__).'/');
define('TEMPLATE', DIR.'template/');
define('DATA', DIR.'data/');

require_once DIR . 'inc/config.php';
require_once DIR . 'inc/class_database.php';
require_once DIR . 'inc/class_input.php';

require_once DIR . 'inc/photo_manager.php';

foreach ($config['db'] as $server => &$config)
{
	DB::setConfig(
		$server,
		$config
	);

	unset($config);
}

DB::wrapConnection(SERVER_SELF, MYSQL_PERSISTENT);
DB::selectDB(DB_MAIN);
DB::$reporterror = true;


$page = $_SERVER['REQUEST_URI'];
$qz_pos = strpos($page, '?');
if ($qz_pos !== false)
	$page = substr($page, 0, $qz_pos);

$PM = new Photo_Manager();

switch ($page)
{
	case '/index.html':
	case '/':
		$PM->load_list();
		$PM->load_tag();
		require_once TEMPLATE . 'INDEX.tpl';
		break;
	case '/filter':
		$PM->load_list_filter();
		$PM->load_tag();
		require_once TEMPLATE . 'INDEX.tpl';
		break;
	case '/load_tag':
		echo $PM->get_tags(Input::cleanGPC('g', 'id', TYPE_UINT));
		break;
	case '/save_tag':
		Input::cleanArrayGPC('p',[
			'id'		=> TYPE_UINT,
			'taglist'	=> TYPE_STR
		]);
		$PM->save_tags(Input::$GPC['id'], Input::$GPC['taglist']);
		break;
	default:
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		echo '['.$page.']';
}


?>