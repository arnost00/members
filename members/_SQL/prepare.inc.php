<?
@extract($_REQUEST);
$this_file_name = 'zmeny_'.$version_upd.'.sql.php';

require_once ('connect.inc.php');
require_once ('../sess.inc.php');
require_once ('common.inc.php');

if (!IsLoggedAdmin())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ('zmeny.inc.sql.php');

if(isset($sql))
	unset($sql);
?>