<?php /* novinky - mazani novinek */
@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");

if (IsLoggedEditor())
{
	db_Connect();
	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;
	echo($id);
	if ($id > 0)
		MySQL_Query('DELETE FROM '.TBL_NEWS.' WHERE `id`=\''.$id.'\'');
	header("location: ".$g_baseadr);
	exit;
}
else
{
	header('location: '.$g_baseadr.'error.php?code=31');
	exit;
}
?>