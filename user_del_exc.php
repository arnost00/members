<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* adminova stranka - provedeni smazani clena */
@extract($_REQUEST);

require_once ('./connect.inc.php');
require_once ('./common.inc.php');
require_once ('./sess.inc.php');
require_once ('./modify_log.inc.php');

if (IsLoggedSmallAdmin())
{
	$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

	if ($id == $usr->user_id)
	{
		header("location: ".$g_baseadr."error.php?code=202");
		exit;
	}

	db_Connect();
	require_once "./common_user.inc.php";

	$user=MySQLi_Fetch_Array(query_db("SELECT u.sort_name, u.reg, u.datum FROM ".TBL_USER." u WHERE u.id = '$id'"));
	$userData = "jmeno = ".$user["sort_name"]." reg = ".$user["reg"]." narozen = ".SQLDate2String($user["datum"]);
	@$vysledekAccount=query_db("DELETE from ".TBL_ACCOUNT." WHERE id_users = '$id'");
	//TODO rozmyslet, zda zalogovat i nepovedene smazani z account
	($vysledekAccount == null)?"":SaveItemToModifyLog_Delete(TBL_ACCOUNT,"id_users = $id $userData");
	@$vysledek=query_db("DELETE FROM ".TBL_USER." WHERE id = '$id'");
	SaveItemToModifyLog_Delete(TBL_USER,"id = $id $userData");
	header("location: ".$g_baseadr."index.php?id=700&subid=1");
}
else
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
?>