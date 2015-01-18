<?php /* novinky - mazani novinek */
@extract($_REQUEST);

require ("connect.inc.php");
require ("sess.inc.php");

if (IsLoggedFinance())
{
	db_Connect();
	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;
	if ($id > 0)
		MySQL_Query('DELETE FROM '.TBL_FINANCE_TYPES.' WHERE `id`=\''.$id.'\'');
	header("location: ".$g_baseadr.'index.php?id=800&subid=4');
	exit;
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>