<?
@extract($_REQUEST);
$this_file_name = 'zmeny_'.$version_upd.'.sql.php';

require ('connect.inc.php');
require ('../sess.inc.php');
require ('common.inc.php');

if (!IsLoggedAdmin())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require ('zmeny.inc.sql.php');

if(isset($sql))
	unset($sql);
?>