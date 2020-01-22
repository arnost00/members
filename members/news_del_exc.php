<?php /* novinky - mazani novinek */
@extract($_REQUEST);

require_once ("connect.inc.php");
require_once ("sess.inc.php");

if (IsLoggedEditor())
{
	db_Connect();
	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;
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