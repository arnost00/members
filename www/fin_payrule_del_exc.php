<?php /* definice plateb - mazani */
$id = $_REQUEST['id'] ?? null;

require_once ("connect.inc.php");
require_once ("sess.inc.php");

if (IsLoggedFinance())
{
	db_Connect();
	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;
	if ($id > 0) {
		$query = 'DELETE FROM '.TBL_PAYRULES.' WHERE `id`=\''.$id.'\'';
		query_db($query);
	}
	header("location: ".$g_baseadr.'index.php?id='._FINANCE_GROUP_ID_.'&subid=5');
	exit;
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>