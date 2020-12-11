<?php /* mazani predef kat */
@extract($_REQUEST);

require_once ("connect.inc.php");
require_once ("sess.inc.php");

if (IsLoggedRegistrator())
{
	db_Connect();
	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;
	if ($id > 0) {
		$query = 'DELETE FROM '.TBL_CATEGORIES_PREDEF.' WHERE `id`=\''.$id.'\'';
		query_db($query);
	}
	header("location: ".$g_baseadr.'categ_predef.php');
	exit;
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>