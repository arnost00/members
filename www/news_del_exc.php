<?php /* novinky - mazani novinek */

require_once ("connect.inc.php");
require_once ("sess.inc.php");

if (IsLoggedEditor())
{
	db_Connect();
	$id = (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) ? (int)$_REQUEST['id'] : 0;
	if ($id > 0) {
		$query = 'DELETE FROM '.TBL_NEWS.' WHERE `id`=\''.$id.'\'';
		query_db($query);
	}
	header("location: ".$g_baseadr);
	exit;
}
else
{
	header('location: '.$g_baseadr.'error.php?code=31');
	exit;
}
?>