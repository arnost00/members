<?php /* mazani radku z mailinfo */
require_once ("connect.inc.php");
require_once ("sess.inc.php");

if (IsLoggedAdmin())
{
	db_Connect();
	$id = (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) ? (int)$_REQUEST['id'] : 0;
	if ($id > 0)
		$query = 'DELETE FROM '.TBL_MAILINFO.' WHERE `id`=\''.$id.'\'';
		query_db($query);
	header("location: ".$g_baseadr."index.php?id=300&subid=8");
	exit;
}
else
{
	header('location: '.$g_baseadr.'error.php?code=31');
	exit;
}
?>