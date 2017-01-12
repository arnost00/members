<?php /* mazani radku z mailinfo */
@extract($_REQUEST);

require_once ("connect.inc.php");
require_once ("sess.inc.php");

if (IsLoggedAdmin())
{
	db_Connect();
	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;
	if ($id > 0)
		MySQL_Query('DELETE FROM '.TBL_MAILINFO.' WHERE `id`=\''.$id.'\'');
	header("location: ".$g_baseadr."index.php?id=300&subid=8");
	exit;
}
else
{
	header('location: '.$g_baseadr.'error.php?code=31');
	exit;
}
?>